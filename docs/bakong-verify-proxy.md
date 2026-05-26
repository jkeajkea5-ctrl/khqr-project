# Bakong Verify Proxy

Use this when the Vercel deployment can generate KHQR successfully but Bakong blocks transaction-status checks from Vercel with CloudFront `403`.

## What this does

- keeps the storefront on Vercel
- moves only `check_transaction_by_md5` to a separate PHP host
- lets the Vercel app call that host through `BAKONG_VERIFY_URL`

## Files

- Proxy endpoint: `deploy/bakong-verify-proxy/index.php`

## Deploy the proxy

Deploy `deploy/bakong-verify-proxy/index.php` to any PHP server that can reach Bakong reliably, such as:

- Cloudways
- shared hosting with PHP 8+
- VPS with Apache / Nginx + PHP

Example URL after deploy:

```text
https://verify.your-domain.com/bakong-verify.php
```

## Environment variables on the proxy host

Set these on the proxy host:

```env
BAKONG_TOKEN=your_real_bakong_token
BAKONG_API_URL=https://api-bakong.nbc.gov.kh
BAKONG_VERIFY_SECRET=your_shared_secret
```

`BAKONG_VERIFY_SECRET` is optional but recommended.

## Environment variables on Vercel

Add these to the Vercel project:

```env
BAKONG_VERIFY_URL=https://verify.your-domain.com/bakong-verify.php
BAKONG_VERIFY_SECRET=the_same_shared_secret
```

After saving them, redeploy the Vercel project.

## Request format

The Vercel app sends:

```json
{
  "md5": "transaction-md5"
}
```

If `BAKONG_VERIFY_SECRET` is set, Vercel also sends:

```text
X-Bakong-Verify-Secret: your_shared_secret
```

## Response format

The proxy returns the Bakong JSON response directly, so the existing checkout flow can keep using:

- `responseCode`
- `responseMessage`
- `data`

## Why this is needed

The current production Vercel deployment reaches the checkout page and generates QR codes, but Bakong transaction-status checks are being blocked upstream from Vercel. The proxy avoids that by moving only the verification request to a host Bakong accepts.
