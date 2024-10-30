jQuery(function($) {
   function check_iframe() {

        var isOverGoogleAd = false;
        $('iframe')
            .mouseover(
            function () {
                isOverGoogleAd = true;
                console.log(isOverGoogleAd);
            }
        )
            .mouseout(
            function () {
                isOverGoogleAd = false;
                console.log(isOverGoogleAd);
            }
        );
        $(window).blur(
            function () {
                if (isOverGoogleAd) {
                    console.log("test")
                }
            }
        ).focus();
    }
});



