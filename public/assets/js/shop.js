/**
 * دیجی‌شاپ — اسکریپت فروشگاه
 * سبد خرید Ajax + اعلان‌ها + انتخاب تعداد
 */

(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    /* ---------- اعلان‌ها ---------- */

    window.dsToast = function (message, type = 'success') {
        let wrap = document.querySelector('.ds-toast-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'ds-toast-wrap';
            document.body.appendChild(wrap);
        }

        const toast = document.createElement('div');
        toast.className = `ds-toast ${type}`;
        toast.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
            <span>${message}</span>
        `;
        wrap.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('hide');
            toast.addEventListener('animationend', () => toast.remove());
        }, 3200);
    };

    /* ---------- درخواست Ajax ---------- */

    async function post(url, data = {}) {
        const body = new FormData();
        Object.entries(data).forEach(([key, value]) => body.append(key, value));

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body,
        });

        if (response.status === 419) {
            dsToast('نشست شما منقضی شده؛ صفحه را رفرش کنید.', 'error');
            throw new Error('CSRF token mismatch');
        }

        return response.json();
    }

    /* ---------- شمارنده سبد در نوار بالا ---------- */

    function updateCartCount(count) {
        document.querySelectorAll('.ds-cart-count').forEach((el) => {
            el.textContent = count.toLocaleString('fa-IR');
            el.style.display = count > 0 ? 'grid' : 'none';
        });
    }

    /* ---------- افزودن به سبد ---------- */

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-add-to-cart]');
        if (!btn) return;

        e.preventDefault();
        btn.disabled = true;

        const quantity = document.querySelector('[data-qty-input]')?.value || 1;

        post(btn.dataset.addToCart, { quantity })
            .then((res) => {
                if (res.success) {
                    dsToast(res.message);
                    updateCartCount(res.count);
                } else {
                    dsToast(res.message || 'خطایی رخ داد.', 'error');
                }
            })
            .catch(() => dsToast('ارتباط با سرور برقرار نشد.', 'error'))
            .finally(() => {
                btn.disabled = false;
            });
    });

    /* ---------- انتخاب تعداد (جزئیات محصول) ---------- */

    document.querySelectorAll('[data-qty]').forEach((group) => {
        const input = group.querySelector('input');
        const max = parseInt(input.max, 10) || 99;

        group.querySelector('[data-qty-plus]')?.addEventListener('click', () => {
            input.value = Math.min(parseInt(input.value, 10) + 1, max);
        });

        group.querySelector('[data-qty-minus]')?.addEventListener('click', () => {
            input.value = Math.max(parseInt(input.value, 10) - 1, 1);
        });
    });

    /* ---------- صفحه سبد خرید ---------- */

    const cartPage = document.querySelector('[data-cart-page]');

    if (cartPage) {
        // تغییر تعداد
        cartPage.querySelectorAll('[data-cart-update]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('[data-cart-row]');
                const input = row.querySelector('[data-qty-input]');
                const product = row.dataset.cartRow;
                const current = parseInt(input.value, 10) || 1;
                const next = btn.dataset.cartUpdate === 'plus' ? current + 1 : current - 1;
                input.value = Math.max(next, 0);

                post(`/cart/update/${product}`, { quantity: input.value })
                    .then((res) => {
                        if (res.success) {
                            if (parseInt(input.value, 10) === 0) {
                                row.style.opacity = '0';
                                setTimeout(() => window.location.reload(), 250);
                            } else {
                                updateCartCount(res.count);
                                window.location.reload();
                            }
                        }
                    })
                    .catch(() => dsToast('ارتباط با سرور برقرار نشد.', 'error'));
            });
        });

        // حذف
        cartPage.querySelectorAll('[data-cart-remove]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('[data-cart-row]');

                post(`/cart/remove/${row.dataset.cartRow}`)
                    .then((res) => {
                        if (res.success) {
                            dsToast(res.message);
                            row.style.transition = 'opacity 0.25s';
                            row.style.opacity = '0';
                            setTimeout(() => window.location.reload(), 400);
                        }
                    })
                    .catch(() => dsToast('ارتباط با سرور برقرار نشد.', 'error'));
            });
        });
    }

    /* ---------- سایدبار ادمین (موبایل) ---------- */

    document.querySelector('[data-sidebar-toggle]')?.addEventListener('click', () => {
        document.querySelector('.ds-sidebar')?.classList.toggle('open');
    });

    /* ---------- پیش‌نمایش تصویر فرم ادمین ---------- */

    const imgInput = document.querySelector('#imageInput');
    const imgPreview = document.querySelector('#imagePreview');

    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', () => {
            const [file] = imgInput.files;
            if (file) {
                imgPreview.src = URL.createObjectURL(file);
                imgPreview.closest('[data-preview-box]').style.display = 'block';
            }
        });
    }
})();
