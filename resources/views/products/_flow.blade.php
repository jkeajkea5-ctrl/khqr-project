
<div class="khqr-page">
    <div class="khqr-bg">
        <span class="blob blob-1"></span>
        <span class="blob blob-2"></span>
        <span class="blob blob-3"></span>
    </div>

    <div class="container py-5 position-relative">
        <header class="hero text-center mb-5">
            <p class="eyebrow">TEAM10 Clothing Store</p>
            <h1 class="display-5 fw-semibold">TEAM10 Clothing Store</h1>
            <p class="lead text-muted">Keyboard Shopping in Cambodia</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                <a href="#catalog" class="btn btn-khqr">Browse products</a>
                @if(isset($product))
                    <a href="#checkout" class="btn btn-ghost">Go To Pay</a>
                @endif
            </div>
        </header>

        <div class="steps mb-5">
            <div class="step {{ isset($products) ? 'is-active' : '' }}">1 Browse</div>
            <div class="step {{ isset($product) ? 'is-active' : '' }}">2 Review</div>
            <div class="step {{ isset($qr) ? 'is-active' : '' }}">3 Pay</div>
        </div>

        @if(isset($products))
        <section id="catalog" class="mb-5">
            <div class="section-header">
                <h2 class="section-title">Product catalog</h2>
                <p class="section-subtitle">Choose a product and continue to payment.</p>
            </div>

            <div class="row g-4">
                @forelse($products as $product)
                <div class="col-lg-4 col-md-6">
                    <div class="khqr-card h-100 product-card">
                        <div class="card-media">
                            <img src="{{ $product->image }}" alt="{{ $product->name }}">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $product->name }}</h5>
                            <p class="card-text">
                                {{ \Illuminate\Support\Str::limit($product->description, 90) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="price">${{ number_format($product->price, 2) }}</span>
                                <a href="{{ route('product.show', $product->id) }}" class="btn btn-khqr btn-sm">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="khqr-card text-center p-5">
                        <p class="text-muted mb-0">No products available.</p>
                    </div>
                </div>
                @endforelse
            </div>
        </section>
        @endif

        @if(isset($product))
        <section id="review" class="mb-5">
            <div class="section-header">
                <h2 class="section-title">Review your product</h2>
                <p class="section-subtitle">Confirm details before generating a QR code.</p>
            </div>

            <div class="khqr-card p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-md-5 text-center">
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" class="detail-image">
                    </div>
                    <div class="col-md-7">
                        <h3 class="fw-semibold">{{ $product->name }}</h3>
                        <p class="text-muted mt-2">{{ $product->description }}</p>

                        <div class="price-box mt-3">
                            <span class="text-muted">Total</span>
                            <div class="price-lg">${{ number_format($product->price, 2) }}</div>
                        </div>

                        @if(!isset($qr))
                        <form action="{{ route('checkout', $product->id) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-khqr btn-lg w-100">Generate QR</button>
                        </form>
                        <p class="text-center text-muted small mt-3 mb-0">Scan with your payment app.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if(isset($product))
        <section id="checkout" class="mb-5">
            <div class="section-header">
                <h2 class="section-title">Go To Pay</h2>
                <p class="section-subtitle">Complete payment by scanning the QR code.</p>
            </div>

            <div class="khqr-card p-4 p-md-5 text-center">
                @if(isset($qr) && $qr)
                    <div class="qr-wrap mx-auto">
                        {!! QrCode::size(280)->generate($qr) !!}
                    </div>
                    <p class="text-muted mt-3 mb-4">Scan this QR code to Pay This Payment.</p>

                    @php
                        $flowExpiresIn = max(60, (int) ($expirySeconds ?? 900));
                        $flowFormattedExpiresIn = sprintf('%02d:%02d', intdiv($flowExpiresIn, 60), $flowExpiresIn % 60);
                    @endphp
                    <div class="timer">
                        <div id="countdown" class="timer-number">{{ $flowFormattedExpiresIn }}</div>
                        <div class="timer-label">This page will expire in <span id="time-left">{{ $flowFormattedExpiresIn }}</span>.</div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">QR code not generated yet. Use the button above to create one.</div>
                @endif

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                    <a href="{{ route('home') }}" class="btn btn-ghost">Back to shop</a>
                    @if(isset($products))
                        <a href="#catalog" class="btn btn-khqr">Browse again</a>
                    @endif
                </div>
            </div>
        </section>
        @endif
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

.khqr-page {
    position: relative;
    min-height: 100vh;
    background: radial-gradient(1000px 500px at 10% 10%, #fef3c7 0%, rgba(254,243,199,0) 60%),
                radial-gradient(900px 400px at 90% 20%, #e0f2fe 0%, rgba(224,242,254,0) 55%),
                #f8fafc;
    color: #0f172a;
    font-family: 'Space Grotesk', system-ui, -apple-system, 'Segoe UI', sans-serif;
}

.khqr-bg .blob {
    position: absolute;
    border-radius: 999px;
    filter: blur(0px);
    opacity: 0.65;
    z-index: 0;
    animation: float 10s ease-in-out infinite;
}

.khqr-bg .blob-1 {
    width: 220px;
    height: 220px;
    background: #fde68a;
    top: 60px;
    left: 6%;
}

.khqr-bg .blob-2 {
    width: 280px;
    height: 280px;
    background: #bae6fd;
    top: 120px;
    right: 8%;
    animation-delay: 1.5s;
}

.khqr-bg .blob-3 {
    width: 180px;
    height: 180px;
    background: #fecaca;
    bottom: 120px;
    left: 18%;
    animation-delay: 3s;
}

.hero .eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.2em;
    font-size: 0.75rem;
    color: #64748b;
}

.hero .lead {
    max-width: 640px;
    margin: 0 auto;
}

.steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    position: relative;
    z-index: 1;
}

.step {
    padding: 12px 16px;
    border-radius: 999px;
    text-align: center;
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(15, 23, 42, 0.08);
    font-weight: 600;
    color: #64748b;
}

.step.is-active {
    color: #0f172a;
    border-color: rgba(14, 116, 144, 0.4);
    background: #e0f2fe;
}

.section-header {
    margin-bottom: 24px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 6px;
}

.section-subtitle {
    color: #64748b;
}

.khqr-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 24px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
    position: relative;
    z-index: 1;
}

.product-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 50px rgba(15, 23, 42, 0.15);
}

.card-media img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
}

.card-body {
    padding: 24px;
}

.card-title {
    font-weight: 600;
}

.card-text {
    color: #64748b;
}

.price {
    font-weight: 600;
    color: #0e7490;
}

.detail-image {
    width: 100%;
    max-width: 260px;
    height: auto;
    border-radius: 18px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.2);
}

.price-box {
    padding: 16px 18px;
    border-radius: 16px;
    background: #f1f5f9;
}

.price-lg {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0e7490;
}

.qr-wrap {
    width: 320px;
    max-width: 100%;
    margin: 0 auto;
    padding: 18px;
    border-radius: 24px;
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    box-shadow: inset 0 0 0 1px rgba(14, 116, 144, 0.08);
}

.timer {
    display: inline-block;
    padding: 16px 24px;
    border-radius: 18px;
    background: #fff7ed;
    border: 1px solid rgba(234, 88, 12, 0.2);
}

.timer-number {
    font-size: 2rem;
    font-weight: 700;
    color: #ea580c;
}

.timer-label {
    color: #9a3412;
    font-size: 0.9rem;
}

.btn-khqr {
    background: #0e7490;
    color: #ffffff;
    border: none;
    padding: 10px 22px;
    border-radius: 999px;
    font-weight: 600;
    box-shadow: 0 10px 20px rgba(14, 116, 144, 0.25);
}

.btn-khqr:hover {
    background: #155e75;
    color: #ffffff;
}

.btn-ghost {
    background: rgba(255, 255, 255, 0.8);
    color: #0f172a;
    border: 1px solid rgba(15, 23, 42, 0.12);
    padding: 10px 22px;
    border-radius: 999px;
    font-weight: 600;
}

.btn-ghost:hover {
    border-color: rgba(14, 116, 144, 0.4);
    color: #0f172a;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-14px); }
}

@media (max-width: 576px) {
    .hero .display-5 {
        font-size: 2rem;
    }

    .qr-wrap {
        width: 100%;
    }
}
</style>

@if(isset($qr) && $qr)
<script>
let timeLeft = {{ max(60, (int) ($expirySeconds ?? 900)) }};
const countdownElement = document.getElementById('countdown');
const timeLeftText = document.getElementById('time-left');

const formatTime = (totalSeconds) => {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};

const timer = setInterval(() => {
    timeLeft--;
    const formattedTime = formatTime(Math.max(0, timeLeft));
    if (countdownElement) countdownElement.textContent = formattedTime;
    if (timeLeftText) timeLeftText.textContent = formattedTime;

    if (timeLeft > 0) {
        fetch("{{ route('verify.transaction') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ md5: "{{ $md5 }}" })
        })
        .then(r => r.json())
        .then(data => {
            if (data.responseCode === 0) {
                clearInterval(timer);
                const orderId = data.order_id || "{{ $orderId ?? '' }}";
                window.location.href = "{{ url('/invoice') }}/" + orderId;
            }
        })
        .catch(console.error);
    }

    if (timeLeft <= 0) {
        clearInterval(timer);
        window.location.href = "{{ route('home') }}";
    }
}, 1000);
</script>
@endif
