@extends('layouts.public')

@section('title', 'Checkout - Oweru Tech Solutions')

@section('content')
<section class="py-16 bg-gray-50 min-h-screen">
    <div class="max-w-lg mx-auto px-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 text-black">
                <h1 class="text-xl font-bold">Secure Checkout</h1>
                <p class="text-sm opacity-80">Powered by PesaPal</p>
            </div>

            {{-- Order Summary --}}
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Order Summary</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-gray-900">{{ $label ?? $payable->name ?? 'Scan Report' }}</p>
                        <p class="text-xs text-gray-500">{{ $description }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-extrabold text-black">TZS {{ number_format($amount) }}</p>
                    </div>
                </div>

                @if(str_starts_with($type, 'invoice-'))
                    <div class="mt-4 bg-yellow-50 rounded-lg p-3 text-xs text-gray-600 space-y-1">
                        <div class="flex justify-between"><span>Invoice total</span><span class="font-semibold">TZS {{ number_format($payable->total, 0) }}</span></div>
                        <div class="flex justify-between"><span>Deposit ({{ $payable->deposit_percent }}%)</span><span class="font-semibold">TZS {{ number_format($payable->deposit_due, 0) }}</span></div>
                        @if($payable->amount_paid > 0)
                            <div class="flex justify-between text-green-700"><span>Already paid</span><span class="font-semibold">TZS {{ number_format($payable->amount_paid, 0) }}</span></div>
                        @endif
                        <div class="flex justify-between border-t border-yellow-200 pt-1"><span>Balance after this payment</span><span class="font-semibold">TZS {{ number_format(max(0, $payable->balance_due - ($type === 'invoice-full' ? $payable->balance_due : $amount)), 0) }}</span></div>
                    </div>
                @endif
            </div>

            {{-- Payment Form --}}
            <form method="POST" action="{{ route('payment.initiate') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="id" value="{{ $payable->id }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="you@example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number (M-Pesa)</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                        placeholder="07XX XXX XXX">
                    <p class="text-xs text-gray-400 mt-1">Required for M-Pesa payments</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        @foreach($errors->all() as $error)
                            <p class="text-xs text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-500 text-black font-bold py-3 rounded-lg transition text-sm">
                    Pay TZS {{ number_format($amount) }} →
                </button>

                <p class="text-center text-xs text-gray-400">
                    You'll be redirected to PesaPal to complete payment via M-Pesa, cards, or mobile money.
                </p>
            </form>
        </div>
    </div>
</section>
@endsection
