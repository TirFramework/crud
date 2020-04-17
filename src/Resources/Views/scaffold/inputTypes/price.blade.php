
@php
    $price = null;
    $placeholder = null;
    if(isset($field->placeholder)){
    $placeholder = $field->placeholder;
    }

    if(isset($item->{$field->name})){
        $price = floor($item->{$field->name});
    }
@endphp


<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group price-group">
        {!! Form::number($field->name,$price ,['class' => 'form-control price' ,'placeholder'=>$placeholder])!!}
        <label for="cloneprice" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>
</div>


@push('scripts')
<script>
    function addCommand(number){
        number = number.toString().replace(/\D/g, "");
        number = number.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
        return number;
    }

    function priceInput(input){
        var PRICE = $(input).val();
        var classname = $(input).attr('class');
        var placeholder = $(input).attr('placeholder');
        if (placeholder == undefined){
            placeholder = '';
        }
        classname = classname.replace('price', "");

        PRICE = addCommand(PRICE);

        // $(input).wrap( '<div class="price-group"></div>' );
        $(input).after('<input class="cloneprice form-control" placeholder="'+placeholder+'" type="text" value="'+PRICE+'" id="cloneprice"/>'
        );

        $(input).hide();
    }

    $(".PRICE, .price").each(function() {
        var PRICE = $(this).html();
        PRICE = PRICE.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
        $(this).html(PRICE);
    });



    function cloneprice(input){
        var oldinput = $(input).parent('.price-group').find("input.price");

        var PRICE = $(input).val();

        PRICE = addCommand(PRICE);

        $(input).val(PRICE);

        oldprice = PRICE.replace(/\D/g, "");

        oldinput.val(oldprice);
    }

    $(document).on('keyup', 'input.cloneprice', function(){
        cloneprice(this);
    });

    $("input.price").each( function () {
        priceInput(this);
    });

</script>
@endpush
