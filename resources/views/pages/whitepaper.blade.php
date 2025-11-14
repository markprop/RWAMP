@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl md:text-5xl font-montserrat font-bold">RWAMP Whitepaper</h1>
                    <p class="text-white/80">Read our comprehensive whitepaper to learn more about RWAMP.</p>
                </div>
                <a href="{{ route('home') }}" class="btn-secondary">Back to Home</a>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 py-10">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <!-- PDF Viewer Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h2 class="text-xl font-montserrat font-bold text-white">Whitepaper-Design.pdf</h2>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('whitepaper.pdf') }}" 
                       target="_blank"
                       class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 backdrop-blur-sm border border-white/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Open in New Tab
                    </a>
                    <a href="{{ asset('Whitepaper-Design.pdf') }}" 
                       download="RWAMP-Whitepaper.pdf"
                       class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 backdrop-blur-sm border border-white/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </a>
                    <button onclick="window.print()" 
                            class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 backdrop-blur-sm border border-white/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            <!-- PDF Viewer Container -->
            <div class="bg-gray-100 p-4" style="min-height: calc(100vh - 300px);">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" style="height: calc(100vh - 320px);">
                    @php
                        $pdfUrl = route('whitepaper.pdf');
                        $pdfDirectUrl = asset('Whitepaper-Design.pdf');
                    @endphp
                    
                    <!-- PDF Viewer - Use embed tag (works better than iframe for PDFs) -->
                    <div class="relative w-full h-full" style="min-height: calc(100vh - 320px); height: calc(100vh - 320px);">
                        <div id="pdf-loading" class="absolute inset-0 flex items-center justify-center bg-gray-100 z-10">
                            <div class="text-center">
                                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary mb-4"></div>
                                <p class="text-gray-600 font-medium">Loading PDF...</p>
                            </div>
                        </div>
                        
                        <!-- Try embed tag first (best for PDFs) -->
                        <embed 
                            src="{{ $pdfUrl }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                            type="application/pdf" 
                            class="w-full h-full border-0 relative z-20"
                            style="min-height: calc(100vh - 320px); height: calc(100vh - 320px); width: 100%;"
                            id="pdf-embed"
                            onload="hideLoading(); // console.log('Embed onload fired');">
                        
                        <!-- Fallback: iframe -->
                        <iframe 
                            src="{{ $pdfUrl }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH" 
                            class="w-full h-full border-0 relative z-20 hidden"
                            style="min-height: calc(100vh - 320px); height: calc(100vh - 320px); width: 100%;"
                            title="RWAMP Whitepaper"
                            id="pdf-iframe"
                            allow="fullscreen"
                            frameborder="0"
                            scrolling="auto"
                            onload="hideLoading(); // console.log('Iframe onload fired');">
                        </iframe>
                    </div>
                    
                    <!-- Fallback message (hidden by default) -->
                    <div id="pdf-fallback" class="hidden p-8 text-center bg-white" style="min-height: calc(100vh - 320px);">
                        <div class="max-w-md mx-auto pt-20">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">PDF Viewer Not Available</h3>
                            <p class="text-gray-600 mb-6">
                                Your browser may not support embedded PDF viewing. Please use one of the options below.
                            </p>
                            <div class="space-y-3">
                                <a href="{{ $pdfUrl }}" 
                                   target="_blank"
                                   class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg w-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Open PDF in New Tab
                                </a>
                                <a href="{{ $pdfDirectUrl }}" 
                                   download="RWAMP-Whitepaper.pdf" 
                                   class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-900 px-6 py-3 rounded-lg text-sm font-semibold transition-all duration-200 w-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Tip:</span> Use the controls above to download or print the whitepaper.
                </p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('whitepaper.pdf') }}" 
                       target="_blank"
                       class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Open in New Tab
                    </a>
                    <a href="{{ asset('Whitepaper-Design.pdf') }}" 
                       download="RWAMP-Whitepaper.pdf"
                       class="inline-flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function hideLoading() {
    const loading = document.getElementById('pdf-loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PDF Viewer Debug ===')
    const embed = document.getElementById('pdf-embed');
    const iframe = document.getElementById('pdf-iframe');
    const fallback = document.getElementById('pdf-fallback');
    const loading = document.getElementById('pdf-loading');
    
    console.log('Embed element:', embed)
    console.log('Iframe element:', iframe)
    console.log('PDF URL:', '{{ route("whitepaper.pdf") }}');
    
    // if (!embed && !iframe) {
        console.error('Neither embed nor iframe found!')
        // return;
    }
    
    // Test if PDF URL is accessible
    // fetch('{{ route("whitepaper.pdf") }}', { method: 'HEAD' })
        .then(response => {
            console.log('PDF Response Status:', response.status)
            console.log('PDF Response Headers:', {
                'Content-Type': response.headers.get('Content-Type'),
                'Content-Disposition': response.headers.get('Content-Disposition'),
                'X-Frame-Options': response.headers.get('X-Frame-Options'),
            });
            
            if (response.status !== 200) {
                console.error('PDF returned non-200 status:', response.status)
                showFallback();
            }
        })
        .catch(error => {
            console.error('PDF Fetch Error:', error)
            showFallback();
        });
    
    // Check embed dimensions
    if (embed) {
        console.log('Embed initial dimensions:', {
            width: embed.offsetWidth,
            height: embed.offsetHeight,
            display: window.getComputedStyle(embed).display,
            visibility: window.getComputedStyle(embed).visibility,
        });
        
        embed.addEventListener('load', function() {
            console.log('Embed load event fired')
            hideLoading();
        });
        
        embed.addEventListener('error', function(e) {
            console.error('Embed error:', e)
            // Try iframe fallback
            if (iframe) {
                embed.style.display = 'none';
                iframe.classList.remove('hidden');
            }
        });
    }
    
    // Check iframe dimensions and styles
    if (iframe) {
        console.log('Iframe initial dimensions:', {
            width: iframe.offsetWidth,
            height: iframe.offsetHeight,
            display: window.getComputedStyle(iframe).display,
            visibility: window.getComputedStyle(iframe).visibility,
        });
    }
    
    // Check parent container
    const container = embed ? embed.parentElement : (iframe ? iframe.parentElement : null);
    console.log('Container dimensions:', {
        width: container ? container.offsetWidth : 0,
        height: container ? container.offsetHeight : 0,
        display: container ? window.getComputedStyle(container).display : 'none',
    });
    
    // Check iframe as fallback
    if (iframe) {
        iframe.addEventListener('load', function() {
            console.log('Iframe load event fired')
            hideLoading();
            
            // Wait a bit then check if PDF is visible
            setTimeout(function() {
                try {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    console.log('Iframe document accessible:', !!iframeDoc)
                    
                    if (iframeDoc) {
                        console.log('Iframe document URL:', iframeDoc.URL)
                        console.log('Iframe document readyState:', iframeDoc.readyState)
                        
                        if (iframeDoc.body) {
                            const bodyText = iframeDoc.body.innerHTML.toLowerCase();
                            console.log('Iframe body content length:', bodyText.length)
                            console.log('Iframe body preview:', bodyText.substring(0, 200));
                            
                            // Check for common error indicators
                            if (bodyText.includes('404') || 
                                bodyText.includes('not found') || 
                                bodyText.includes('error') ||
                                bodyText.includes('failed to load') ||
                                bodyText.includes('this pdf cannot be displayed')) {
                                console.error('PDF failed to load - error detected in iframe')
                                showFallback();
                            } else {
                                console.log('PDF appears to be loaded successfully in iframe')
                            }
                        }
                    }
                } catch (e) {
                    // Cross-origin or other error - this is normal for PDFs
                    console.log('Cannot access iframe content (normal for PDFs):', e.message);
                    console.log('Assuming PDF is loading/loaded if iframe has dimensions')
                    
                    // Check if iframe has proper dimensions
                    if (iframe.offsetHeight > 0 && iframe.offsetWidth > 0) {
                        console.log('Iframe has dimensions - PDF likely loaded')
                    } else {
                        console.warn('Iframe has no dimensions - PDF may not be loading')
                    }
                }
            }, 1000);
        });
        
        iframe.addEventListener('error', function(e) {
            console.error('Iframe error event:', e)
            hideLoading();
            showFallback();
        });
    }
    
    // Hide loading after a delay
    setTimeout(function() {
        console.log('Initial timeout - hiding loading')
        hideLoading();
    }, 3000);
    
    // Final check after longer timeout
    setTimeout(function() {
        console.log('Final check timeout')
        hideLoading();
        
        const embedVisible = embed && embed.offsetHeight > 0 && embed.offsetWidth > 0;
        const iframeVisible = iframe && iframe.offsetHeight > 0 && iframe.offsetWidth > 0;
        
        console.log('Final visibility check:', {
            embedVisible: embedVisible,
            embedHeight: embed ? embed.offsetHeight : 0,
            embedWidth: embed ? embed.offsetWidth : 0,
            iframeVisible: iframeVisible,
            iframeHeight: iframe ? iframe.offsetHeight : 0,
            iframeWidth: iframe ? iframe.offsetWidth : 0,
        })
        
        if (!embedVisible && !iframeVisible) {
            console.warn('Both embed and iframe appear empty - showing fallback')
            showFallback();
        }
    }, 8000);
    
    function showFallback() {
        console.log('Showing fallback message')
        if (embed) embed.style.display = 'none';
        if (iframe) iframe.style.display = 'none';
        if (fallback) fallback.classList.remove('hidden');
    }
});
</script>

<style>
embed, iframe {
    width: 100%;
    height: 100%;
    min-height: calc(100vh - 320px);
,
}

@media print {
    .bg-gradient-to-r,
    .bg-gray-100,
    .bg-gray-50,
    nav,
    .btn-secondary {
        display: none !important;
,
    }
    embed, iframe {
        height: 100vh !important;
        min-height: 100vh !important;
,
    }
}
</style>
@endsection

