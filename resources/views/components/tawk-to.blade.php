@auth
@if(config('services.tawk.enabled') && config('services.tawk.property_id') && config('services.tawk.widget_id'))
<!--Start of Tawk.to Script - Only for authenticated users-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/{{ config('services.tawk.property_id') }}/{{ config('services.tawk.widget_id') }}';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();

// Set user attributes for authenticated users (for ticketing support)
// Note: Tawk.to doesn't accept 'user_id' as a key, using 'hash' instead for user identification
Tawk_API.onLoad = function(){
    if (window.Tawk_API && window.Tawk_API.setAttributes) {
        Tawk_API.setAttributes({
            'name': '{{ auth()->user()->name ?? "" }}',
            'email': '{{ auth()->user()->email ?? "" }}',
            'hash': '{{ md5(auth()->user()->email ?? "") }}',
            'role': '{{ auth()->user()->role ?? "guest" }}'
        }, function(error){
            if (error) {
                console.error('Tawk.to: Failed to set user attributes', error);
            }
        });
    }
};
</script>
<!--End of Tawk.to Script-->
@endif
@endauth

