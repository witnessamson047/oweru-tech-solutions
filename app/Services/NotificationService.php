<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Recipients for internal alerts: every admin + the enquiry owner (if set).
     */
    protected function recipients(Enquiry $enquiry): array
    {
        $admins = User::where('role', 'admin')->pluck('email')->all();

        if ($enquiry->owner_id) {
            $owner = User::find($enquiry->owner_id);
            if ($owner && !in_array($owner->email, $admins)) {
                $admins[] = $owner->email;
            }
        }

        // Fallback so alerts are never silently dropped
        if (empty($admins)) {
            $admins = [config('mail.from.address', 'info@oweru.co.tz')];
        }

        return $admins;
    }

    /**
     * Alert staff that a new enquiry arrived. Sends email and records the
     * notification in the notifications table for the admin history.
     */
    public function newEnquiry(Enquiry $enquiry): void
    {
        $package = $enquiry->package_name ?? 'general';
        $subject = "New enquiry: {$enquiry->business_name} ({$package})";
        $message = sprintf(
            "%s from %s submitted an enquiry.\n\nService/Package: %s\nBudget: %s\nRequired date: %s\nProblem: %s\n\nOpen the admin dashboard to qualify this lead.",
            $enquiry->name,
            $enquiry->business_name,
            $enquiry->package_name ?? '-',
            $enquiry->budget_range ?? '-',
            $enquiry->required_date ?? '-',
            str($enquiry->problem_description)->limit(300),
        );

        foreach ($this->recipients($enquiry) as $email) {
            $this->dispatchNotification(
            enquiryId: $enquiry->id,
                type: 'new_enquiry',
                recipientEmail: $email,
                subject: $subject,
                message: $message,
            );
        }
    }

    /**
     * Remind staff about enquiries stuck in one stage for 3+ working days.
     * Returns the number of reminders recorded.
     */
    public function stageReminders(int $workingDays = 3): int
    {
        // Approximate working days: skip Sat/Sun by subtracting 7 calendar days
        // for every 5 working days requested (3 working days ≈ 5 calendar days).
        $cutoff = now()->subDays((int) ceil($workingDays * 7 / 5));

        $stale = Enquiry::where('stage', '!=', 'won')
            ->where('stage', '!=', 'lost')
            ->where('last_stage_changed_at', '<', $cutoff)
            ->orWhereNull('last_stage_changed_at')
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;
        foreach ($stale as $enquiry) {
            $subject = "Reminder: {$enquiry->business_name} stuck in '{$enquiry->stage}' for {$workingDays}+ working days";
            $message = sprintf(
                "The enquiry from %s (%s) has been in stage '%s' since %s with no movement.\n\nFollow up to keep the pipeline moving.",
                $enquiry->name,
                $enquiry->business_name,
                $enquiry->stage,
                optional($enquiry->last_stage_changed_at ?? $enquiry->created_at)->format('d M Y'),
            );

            foreach ($this->recipients($enquiry) as $email) {
                $this->dispatchNotification(
                    enquiryId: $enquiry->id,
                    type: 'stage_reminder',
                    recipientEmail: $email,
                    subject: $subject,
                    message: $message,
                );
            }
            $count++;
        }

        return $count;
    }

    /**
     * Record the notification row, then attempt delivery. Email failures are
     * captured on the record instead of breaking the caller.
     */
    protected function dispatchNotification(
        ?int $enquiryId,
        string $type,
        string $recipientEmail,
        string $subject,
        string $message,
        ?int $recipientId = null,
        string $channel = 'email',
    ): Notification {
        $notification = Notification::create([
            'enquiry_id' => $enquiryId,
            'type' => $type,
            'recipient_id' => $recipientId,
            'recipient_email' => $recipientEmail,
            'channel' => $channel,
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
        ]);

        try {
            Mail::raw($message, function ($mail) use ($recipientEmail, $subject) {
                $mail->to($recipientEmail)->subject($subject);
            });

            $notification->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Notification email failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);

            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $notification;
    }
}
