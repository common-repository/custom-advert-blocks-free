(function($){
    $.fn.undoredo = function(action)
    {        
        if (action === "undo")
        {        
            if ($(this).data('undostack').length > 1)
            {
                $(this).focus();
                $(this).data('redostack').push($(this).data('undostack').pop());
                $(this).val($(this).data('undostack').pop());
                $(this).data('undostack').push($(this).val());
                $(this).trigger('undoredo');
            }
            return this;
        }
        
        if (action === "redo")
        {
            if ($(this).data('redostack').length > 0)
            {
                $(this).focus();
                $(this).val($(this).data('redostack').pop());
                $(this).data('undostack').push($(this).val());
                $(this).trigger('undoredo');
            }
            return this;
        }

        $(this).data('undostack', [$(this).val()]);
        $(this).data('redostack', []);
        
        $(this).change(function(){        
            $(this).data('undostack').push($(this).val());
            $(this).data('redostack', []);
            $(this).trigger('undoredo');
        });        
        
        return this;
    };
})(jQuery);   
