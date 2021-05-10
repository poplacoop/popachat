$(document).ready(function() {
    $('#myRangeBar').change(function() {
            console.log("#Modification d'intervalle "+$(this).val());
            $('[name="myRange"]').val($(this).val());
            $('[name="myForm"]').submit();
        });
        $('[name="myRange"]').change(function() {
            console.log(".Modification d'intervalle "+$(this).val());
            $('[name="myRangeBar"]').val($(this).val());
            $('[name="myForm"]').submit();
    });
    
});
