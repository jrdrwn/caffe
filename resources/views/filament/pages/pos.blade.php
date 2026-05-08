@php
/** @var array $products */
@endphp

<div class="filament-page">
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-8">
            <div class="grid grid-cols-3 gap-4">
                @foreach($products as $product)
                    <div class="p-4 bg-white rounded-lg shadow hover:shadow-md flex flex-col">
                        @if(!empty($product['image_url']))
                            <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="h-32 w-full object-cover rounded">
                        @else
                            <div class="h-32 w-full bg-gray-100 rounded flex items-center justify-center">No Image</div>
                        @endif
                        <div class="mt-3 flex-1">
                            <div class="font-semibold">{{ $product['name'] }}</div>
                            <div class="text-sm text-gray-500">SKU: {{ $product['sku'] ?? '-' }}</div>
                            <div class="mt-2 font-medium">Rp {{ number_format($product['price'], 0, ',', '.') }}</div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded add-to-cart" data-product='@json($product)'>Add</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-span-4">
            <div class="p-4 bg-white rounded-lg shadow">
                <h3 class="text-lg font-semibold">Cart</h3>
                <div id="cart-list" class="mt-4 space-y-2">
                    <div class="text-sm text-gray-500">No items in cart</div>
                </div>

                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span id="cart-subtotal">Rp 0</span></div>
                        <div class="mt-3">
                            <button id="checkout-btn" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">Checkout (Fake Gateway)</button>
                        </div>
                    </div>
            </div>
            <div class="mt-4 text-xs text-gray-500">Note: This is a client-side POS skeleton. Checkout currently uses a fake gateway flow (no backend persistence yet).</div>
        </div>
    </div>
</div>

<script>
    (() => {
        const cart = [];
        const cartList = document.getElementById('cart-list');
        const subtotalEl = document.getElementById('cart-subtotal');

        function renderCart() {
            cartList.innerHTML = '';
            if (cart.length === 0) {
                cartList.innerHTML = '<div class="text-sm text-gray-500">No items in cart</div>';
                subtotalEl.textContent = 'Rp 0';
                return;
            }
            let subtotal = 0;
            cart.forEach((item, idx) => {
                subtotal += item.price * item.qty;
                const row = document.createElement('div');
                row.className = 'flex items-center justify-between';
                row.innerHTML = `<div class="text-sm">${item.name} x ${item.qty}</div><div class="text-sm font-medium">Rp ${item.price.toLocaleString('id-ID')}</div>`;
                cartList.appendChild(row);
            });
            subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const product = JSON.parse(btn.getAttribute('data-product'));
                const existing = cart.find(c => c.id === product.id);
                if (existing) existing.qty += 1; else cart.push({id: product.id, name: product.name, price: Number(product.price), qty: 1});
                renderCart();
            });
        });

        document.getElementById('checkout-btn').addEventListener('click', async () => {
            if (cart.length === 0) { alert('Cart kosong'); return; }
            const total = cart.reduce((s,i)=>s+i.price*i.qty,0);
            const proceed = confirm('Proceed to fake payment of Rp ' + total.toLocaleString('id-ID') + '?');
            if (!proceed) return;

            try {
                const res = await fetch('{{ route('pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ cart, payment_method: 'cash' }),
                });

                if (!res.ok) {
                    const err = await res.json();
                    alert('Checkout failed: ' + (err.message || 'Unknown'));
                    return;
                }

                const data = await res.json();
                alert('Payment success (fake). Transaction: ' + data.transaction_number);
                cart.length = 0;
                renderCart();
            } catch (e) {
                console.error(e);
                alert('Checkout error');
            }
        });

        renderCart();
    })();
</script>
