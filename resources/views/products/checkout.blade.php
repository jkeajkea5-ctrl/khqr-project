@extends('layouts.app')

@section('styles')
@include('products._styles')
@endsection

@section('content')
@include('products._nav')
<div class="shop-page">
    <div class="container py-5 text-center">
        <div class="mb-4">
            <h2 class="section-title">Go To Pay</h2>
            <p class="section-subtitle">Scan the QR code to complete payment.</p>
        </div>

        <div class="card-khqr p-4 p-md-5 checkout-card">
            @php
                $currencyCode = strtoupper((string) ($currency ?? 'USD'));
                $formatAmount = function (float $amount) use ($currencyCode): string {
                    return $currencyCode === 'KHR'
                        ? number_format($amount, 0).' KHR'
                        : '$'.number_format($amount, 2);
                };
                $formattedTotal = $currencyCode === 'KHR'
                    ? number_format((float) ($total ?? 0), 0).' KHR'
                    : '$'.number_format((float) ($total ?? 0), 2);
                $expiresIn = max(60, (int) ($expirySeconds ?? 900));
                $formattedExpiresIn = sprintf('%02d:%02d', intdiv($expiresIn, 60), $expiresIn % 60);
            @endphp
            @if(!empty($items ?? []))
                <div class="row g-4">
                    <div class="col-lg-6 text-start">
                        <h5 class="fw-semibold">Order summary</h5>
                        <ul class="list-unstyled mt-3">
                            @foreach($items as $item)
                                <li class="d-flex justify-content-between border-bottom py-2">
                                    <div>
                                        <div>{{ $item['name'] }} (x{{ $item['qty'] }})</div>
                                        <div class="text-muted small">Size: {{ $item['size'] ?? 'N/A' }}, Color: {{ $item['color'] ?? 'N/A' }}</div>
                                    </div>
                                    <span>{{ $formatAmount((float) ($item['price'] * $item['qty'])) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="summary-card mt-3">
                            <div class="text-muted small">Total</div>
                            <div class="fs-4 fw-semibold">{{ $formattedTotal }}</div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        @if(isset($qr) && $qr)
                            <div class="qr-box">
                                {!! QrCode::size(260)->generate($qr) !!}
                            </div>
                            @if(!empty($billNumber ?? null))
                                <div class="mt-3 small fw-semibold">Reference: {{ $billNumber }}</div>
                            @endif
                            <p class="text-muted mt-3">Scan this QR code to Pay This Payment.</p>

                            <div class="summary-card d-inline-block mt-2">
                                <div id="countdown" class="timer-number">{{ $formattedExpiresIn }}</div>
                                <div class="text-muted">This page will expire in <span id="time-left">{{ $formattedExpiresIn }}</span>.</div>
                            </div>

                            <div id="verification-status" class="small text-muted mt-3">
                                Waiting for Bakong payment confirmation...
                            </div>
                            <div id="verification-error" class="alert alert-warning mt-3 text-start d-none" role="alert"></div>
                            <button id="retry-verification" type="button" class="btn btn-ghost btn-sm mt-2">
                                Retry verification
                            </button>
                        @else
                            <div class="alert alert-warning mb-2">Failed to generate QR.</div>
                            @if(!empty($qrError ?? null))
                                <div class="small text-muted text-start">Reason: {{ $qrError }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">No items ready for payment.</p>
            @endif
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
            <a href="{{ route('cart.index') }}" class="btn btn-ghost">Back to cart</a>
            <a href="{{ route('home') }}" class="btn btn-ghost">Back to shop</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(isset($qr) && $qr)
<script>
let timeLeft = {{ $expiresIn }};
const countdownElement = document.getElementById('countdown');
const timeLeftText = document.getElementById('time-left');
const statusElement = document.getElementById('verification-status');
const errorElement = document.getElementById('verification-error');
const retryButton = document.getElementById('retry-verification');
const verifyUrl = @json(route('verify.transaction'));
const csrfToken = @json(csrf_token());
const md5 = @json($md5);
const fallbackOrderId = @json((string) ($orderId ?? ''));
const invoiceBaseUrl = @json(url('/invoice'));
const homeUrl = @json(route('home'));
let verifyInFlight = false;
let verificationEnabled = true;

const formatTime = (totalSeconds) => {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};

const setStatus = (message) => {
    if (statusElement) {
        statusElement.textContent = message;
    }
};

const setVerificationError = (message) => {
    if (!errorElement) return;
    errorElement.textContent = message;
    errorElement.classList.remove('d-none');
};

const clearVerificationError = () => {
    if (!errorElement) return;
    errorElement.textContent = '';
    errorElement.classList.add('d-none');
};

const isConfigurationError = (message) => /not configured/i.test(message);

const pollVerification = async () => {
    if (verifyInFlight || !verificationEnabled) return;

    verifyInFlight = true;

    try {
        const response = await fetch(verifyUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ md5 })
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.error || 'Unable to verify payment right now.');
        }

        clearVerificationError();

        if (data.responseCode === 0) {
            clearInterval(timer);
            setStatus('Payment confirmed. Redirecting to your invoice...');
            const orderId = data.order_id || fallbackOrderId;
            if (!orderId) {
                throw new Error('Payment was confirmed, but the invoice link is missing.');
            }
            window.location.href = invoiceBaseUrl + "/" + orderId;
            return;
        }

        if (data.verificationUnavailable) {
            verificationEnabled = false;
            clearInterval(timer);
            setStatus('Bakong verification is temporarily unavailable on this deployment.');
            setVerificationError(data.error || 'Unable to verify payment right now.');

            if (retryButton) {
                retryButton.textContent = 'Try again later';
            }

            return;
        }

        setStatus('QR generated. Waiting for Bakong payment confirmation...');
    } catch (error) {
        const message = error.message || 'Unable to verify payment right now.';

        if (isConfigurationError(message)) {
            verificationEnabled = false;
            clearInterval(timer);
            setStatus('Bakong verification is not configured for this deployment.');
            if (retryButton) {
                retryButton.disabled = true;
            }
        } else {
            setStatus('Bakong verification is temporarily unavailable.');
        }

        setVerificationError(message);
    } finally {
        verifyInFlight = false;
    }
};

const timer = setInterval(() => {
    timeLeft--;
    const formattedTime = formatTime(Math.max(0, timeLeft));
    if (countdownElement) countdownElement.textContent = formattedTime;
    if (timeLeftText) timeLeftText.textContent = formattedTime;

    if (timeLeft > 0 && verificationEnabled) {
        pollVerification();
    }

    if (timeLeft <= 0) {
        clearInterval(timer);
        window.location.href = homeUrl;
    }
}, 1000);

if (retryButton) {
    retryButton.addEventListener('click', pollVerification);
}

pollVerification();
</script>
@endif
@endsection
