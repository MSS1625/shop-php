{{-- دکمهٔ مقایسه — مشترک بین کارت محصول و صفحهٔ جزئیات؛ مهمان هم می‌تواند (مبتنی بر سشن) --}}
@inject('compare', 'App\Services\Compare')

@php
    $inCompare = $compare->has($product->id);
    $compareLabel = $inCompare ? 'حذف از مقایسه' : 'افزودن به مقایسه';
    $big = isset($big);
@endphp

<form action="{{ route('compare.toggle', $product) }}" method="POST"
      class="ds-compare-form {{ $big ? 'ds-compare-form-lg' : '' }}"
      data-compare-toggle data-compare-product="{{ $product->slug }}">
    @csrf
    <button type="submit"
            class="ds-compare-btn {{ $inCompare ? 'active' : '' }} {{ $big ? 'ds-compare-btn-lg' : '' }}"
            aria-label="{{ $compareLabel }}" title="{{ $compareLabel }}">
        <i class="fa-solid fa-scale-balanced"></i>
    </button>
</form>
