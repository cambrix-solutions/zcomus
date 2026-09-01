{{--
    DEV/TESTING ONLY — not part of the app's real UI. Delete this file
    and its route once the SPA has its own real checkout page built on
    the same /api/payments/initiate + /api/payments/{id}/status pair.

    Self-contained on purpose: logs in via fetch (no separate Blade
    login view exists in this API-first project), submits ABA's real
    checkout form, and polls our own status endpoint — everything
    needed to test one real payment end to end from a single page.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ABA PayWay — Test Page (dev only)</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 480px; margin: 40px auto; padding: 0 16px; color: #222; }
        h2 { margin-top: 32px; border-bottom: 1px solid #ddd; padding-bottom: 8px; }
        section { margin-bottom: 24px; }
        label { display: block; font-size: 13px; color: #555; margin-top: 8px; }
        input { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 12px; padding: 10px 16px; cursor: pointer; }
        #status-log { background: #f5f5f5; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 13px; white-space: pre-wrap; min-height: 60px; }
        .ok { color: #0a7d2c; }
        .err { color: #b00020; }
    </style>
</head>
<body>
    <h1>ABA PayWay — Test Page</h1>
    <p><em>Dev only. Delete before shipping.</em></p>

    <section>
        <h2>1. Log in</h2>
        <label>Email <input type="email" id="login_email" value="sophea@customer.zcomus.test"></label>
        <label>Password <input type="password" id="login_password" value="password"></label>
        <button onclick="login()">Log in</button>
        <div id="login-status"></div>
    </section>

    <section>
        <h2>2. Start ABA checkout</h2>
        <label>Order ID <input type="number" id="order_id" value=""></label>
        <button onclick="startAbaCheckout(document.getElementById('order_id').value)">Start ABA Checkout</button>
    </section>

    <section>
        <h2>3. Status</h2>
        <div id="status-log">Not started yet.</div>
    </section>

    {{-- Hidden form ABA's plugin submits — field values are filled in by startAbaCheckout() below --}}
    <form method="POST" target="aba_webservice" id="aba_merchant_request">
        <input type="hidden" name="hash" id="hash" />
        <input type="hidden" name="tran_id" id="tran_id" />
        <input type="hidden" name="amount" id="amount" />
        <input type="hidden" name="firstname" id="firstname" />
        <input type="hidden" name="lastname" id="lastname" />
        <input type="hidden" name="phone" id="phone" />
        <input type="hidden" name="email" id="email" />
        <input type="hidden" name="req_time" id="req_time" />
        <input type="hidden" name="merchant_id" id="merchant_id" />
        <input type="hidden" name="payment_option" id="payment_option" value="abapay_khqr" />
    </form>

    <script src="https://checkout.payway.com.kh/plugins/checkout2-0.js"></script>
    <script>
        const log = (msg, cls = '') => {
            const el = document.getElementById('status-log');
            const line = document.createElement('div');
            if (cls) line.className = cls;
            line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
            el.prepend(line);
        };

        async function login() {
            const email = document.getElementById('login_email').value;
            const password = document.getElementById('login_password').value;

            const res = await fetch('/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ email, password }),
            });

            const loginStatus = document.getElementById('login-status');
            if (res.ok) {
                loginStatus.innerHTML = '<span class="ok">Logged in.</span>';
                log('Login OK', 'ok');
            } else {
                const body = await res.json().catch(() => ({}));
                loginStatus.innerHTML = `<span class="err">Login failed: ${body.message || res.status}</span>`;
                log(`Login failed: ${JSON.stringify(body)}`, 'err');
            }
        }

        async function startAbaCheckout(orderId) {
            if (!orderId) {
                log('Enter an order ID first.', 'err');
                return;
            }

            log(`Calling /api/payments/initiate for order ${orderId}...`);

            const res = await fetch('/api/payments/initiate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ order_id: Number(orderId) }),
            });

            const body = await res.json().catch(() => ({}));

            if (!res.ok) {
                log(`initiate failed (${res.status}): ${body.message || JSON.stringify(body)}`, 'err');
                return;
            }

            const c = body.data.checkout;
            log(`Got signed checkout fields for tran_id ${c.tran_id}, amount ${c.amount}. Opening ABA checkout...`, 'ok');

            document.getElementById('aba_merchant_request').action = c.api_url;
            document.getElementById('hash').value = c.hash;
            document.getElementById('tran_id').value = c.tran_id;
            document.getElementById('amount').value = c.amount;
            document.getElementById('firstname').value = c.firstname;
            document.getElementById('lastname').value = c.lastname;
            document.getElementById('phone').value = c.phone;
            document.getElementById('email').value = c.email;
            document.getElementById('req_time').value = c.req_time;
            document.getElementById('merchant_id').value = c.merchant_id;

            AbaPayway.checkout();

            pollPaymentStatus(body.data.id);
        }

        async function pollPaymentStatus(paymentId) {
            const res = await fetch(`/api/payments/${paymentId}/status`, {
                headers: { Accept: 'application/json' },
                credentials: 'include',
            });
            const body = await res.json().catch(() => ({}));

            if (!res.ok) {
                log(`status check failed (${res.status}): ${body.message || JSON.stringify(body)}`, 'err');
                return;
            }

            const status = body.data.status;
            log(`Payment ${paymentId} status: ${status}`);

            if (status === 'completed') {
                log(`Paid at ${body.data.paid_at}`, 'ok');
                return;
            }
            if (status === 'failed') {
                log('Payment failed.', 'err');
                return;
            }

            setTimeout(() => pollPaymentStatus(paymentId), 3000);
        }
    </script>
</body>
</html>
