@php
    use App\Helpers\PriceHelper;
    
    // Ensure pkr is a float
    $pkr = (float) ($pkr ?? 0);
    
    // Get exchange rates
    $usdPkr = PriceHelper::getUsdToPkrRate();
    $aedPkr = PriceHelper::getAedToPkrRate();
    
    // Calculate USD and AED equivalents (USD is primary, so calculate from PKR)
    $usd = $usdPkr > 0 ? $pkr / $usdPkr : 0;
    $aed = $aedPkr > 0 ? $pkr / $aedPkr : 0;
    
    // Format values
    $pkrFormatted = number_format($pkr, 2);
    $usdFormatted = $usd > 0 ? number_format($usd, 2) : null;
    $aedFormatted = $aed > 0 ? number_format($aed, 2) : null;
    
    // Determine layout based on screen size
    $layout = $layout ?? 'auto'; // 'auto', 'inline', 'stacked'
    $size = $size ?? 'normal'; // 'small', 'normal', 'large'
    $variant = $variant ?? 'light'; // 'light', 'dark' (for light/dark backgrounds)
    
    // Size classes
    $primarySizeClass = match($size) {
        'small' => 'text-sm',
        'large' => 'text-2xl md:text-3xl',
        default => 'text-base md:text-lg'
    };
    
    $secondarySizeClass = match($size) {
        'small' => 'text-xs',
        'large' => 'text-sm',
        default => 'text-xs'
    };
    
    // Color classes based on variant
    $primaryColorClass = $variant === 'dark' ? 'text-white' : 'text-gray-900';
    $secondaryColorClass = $variant === 'dark' ? 'text-white/80' : 'text-gray-500';
@endphp

@if($pkr > 0)
    <div class="price-tag {{ $class ?? '' }} overflow-hidden" @if(isset($xData)) x-data="{{ $xData }}" @endif>
        {{-- Primary USD Display --}}
        @if($usdFormatted)
            <div class="font-bold {{ $primarySizeClass }} {{ $primaryColorClass }} break-words break-all leading-tight">
                <span class="inline-block max-w-full truncate">USD ${{ $usdFormatted }}</span>
            </div>
        @else
            {{-- Fallback to PKR if USD calculation fails --}}
            <div class="font-bold {{ $primarySizeClass }} {{ $primaryColorClass }} break-words break-all leading-tight">
                <span class="inline-block max-w-full truncate">PKR {{ $pkrFormatted }}</span>
            </div>
        @endif
        
        {{-- Secondary AED & PKR Display --}}
        @if($usdFormatted && ($aedFormatted || $pkrFormatted))
            <div class="mt-0.5 sm:mt-1 flex flex-col sm:flex-row sm:items-center sm:gap-1.5 {{ $secondarySizeClass }} {{ $secondaryColorClass }} flex-wrap gap-x-1.5">
                @if($aedFormatted)
                    <span class="whitespace-nowrap break-keep">AED {{ $aedFormatted }}</span>
                @endif
                @if($aedFormatted && $pkrFormatted)
                    <span class="hidden sm:inline flex-shrink-0">·</span>
                @endif
                @if($pkrFormatted)
                    <span class="whitespace-nowrap break-keep overflow-hidden text-ellipsis">PKR {{ $pkrFormatted }}</span>
                @endif
            </div>
        @elseif(!$usdFormatted && ($aedFormatted || $pkrFormatted))
            {{-- If USD not available, show AED as primary, PKR as secondary --}}
            @if($aedFormatted)
                <div class="mt-0.5 sm:mt-1 {{ $secondarySizeClass }} {{ $secondaryColorClass }} flex flex-wrap gap-x-1.5">
                    <span class="whitespace-nowrap break-keep">AED {{ $aedFormatted }}</span>
                    @if($pkrFormatted)
                        <span class="hidden sm:inline flex-shrink-0">·</span>
                        <span class="whitespace-nowrap break-keep overflow-hidden text-ellipsis">PKR {{ $pkrFormatted }}</span>
                    @endif
                </div>
            @endif
        @endif
    </div>
@else
    <span class="text-gray-400">—</span>
@endif

