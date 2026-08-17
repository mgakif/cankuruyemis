<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>İçecek Kuponu {{ $coupon->code }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f6f3ed;
            color: #1f1a17;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 18px;
        }
        main {
            width: min(100%, 430px);
            background: #fff;
            border: 1px solid #e8dfd0;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 16px 45px rgba(31, 26, 23, .08);
        }
        h1 {
            margin: 0 0 12px;
            font-size: 24px;
            letter-spacing: 0;
        }
        .status {
            padding: 12px 14px;
            border-radius: 10px;
            margin: 12px 0;
            background: #edf8ef;
            color: #115b25;
            font-weight: 700;
        }
        .error {
            background: #fff1f1;
            color: #9c1c1c;
        }
        .metric {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 13px 0;
            border-bottom: 1px solid #eee6da;
        }
        .metric strong {
            font-size: 19px;
        }
        .code {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        form {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }
        button, a.button {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 17px;
            font-weight: 800;
            background: #1f1a17;
            color: #fff;
            text-align: center;
            text-decoration: none;
        }
        .secondary {
            background: #7f5b2e;
        }
        textarea {
            width: 100%;
            box-sizing: border-box;
            min-height: 70px;
            border-radius: 10px;
            border: 1px solid #d7caba;
            padding: 10px;
            font: inherit;
        }
        .muted {
            color: #73685e;
            font-size: 14px;
        }
    </style>
</head>
<body>
<main>
    <p class="muted">CAN KURUYEMİŞ</p>
    <h1>İÇECEK KUPONU</h1>

    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif

    @if ($errors->has('coupon'))
        <div class="status error">{{ $errors->first('coupon') }}</div>
    @endif

    @php
        $publicStatus = $coupon->publicStatusLabel();
        $expired = $publicStatus === \App\Models\Coupon::STATUS_EXPIRED;
        $canUse = $coupon->status === \App\Models\Coupon::STATUS_ACTIVE && $coupon->remaining_quantity > 0;
        $useRoute = $mode === 'token'
            ? route('coupons.use', ['token' => $identifier])
            : route('coupons.code.use', ['code' => $identifier]);
        $overrideRoute = $mode === 'token'
            ? route('coupons.override-use', ['token' => $identifier])
            : route('coupons.code.override-use', ['code' => $identifier]);
    @endphp

    <div class="metric">
        <span>Kalan Hak</span>
        <strong>{{ $coupon->remaining_quantity }}</strong>
    </div>
    <div class="metric">
        <span>Son Kullanım</span>
        <strong>{{ $coupon->expires_at->format('d.m.Y') }}</strong>
    </div>
    <div class="metric">
        <span>Durum</span>
        <strong>{{ match($publicStatus) {
            'active' => 'Aktif',
            'fully_used' => 'Tamamen Kullanıldı',
            'expired' => 'Süresi Doldu',
            'cancelled' => 'İptal',
            default => $publicStatus,
        } }}</strong>
    </div>

    <p class="muted">Kupon</p>
    <div class="code">{{ $coupon->code }}</div>

    @if ($coupon->status === \App\Models\Coupon::STATUS_FULLY_USED || $coupon->remaining_quantity === 0)
        <div class="status error">Bu kupon tamamen kullanılmıştır.</div>
    @elseif ($coupon->status === \App\Models\Coupon::STATUS_CANCELLED)
        <div class="status error">Bu kupon iptal edilmiştir.</div>
    @elseif ($expired)
        <div class="status error">Kuponun süresi dolmuş.</div>
    @endif

    @auth
        @if ($canUse && ! $expired)
            @for ($i = 1; $i <= min(3, $coupon->remaining_quantity); $i++)
                <form method="POST" action="{{ $useRoute }}">
                    @csrf
                    <input type="hidden" name="quantity" value="{{ $i }}">
                    <button type="submit">{{ $i }} İçecek Kullan</button>
                </form>
            @endfor
        @elseif ($canUse && $expired)
            <form method="POST" action="{{ $overrideRoute }}">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <textarea name="note" placeholder="İstisna notu"></textarea>
                <button class="secondary" type="submit">İstisnai Olarak Kullan</button>
            </form>
        @endif
    @else
        <a class="button" href="{{ url('/admin/login') }}">Personel Girişi</a>
    @endauth
</main>
</body>
</html>
