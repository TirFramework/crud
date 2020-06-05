
@php
    $price = null;
    $placeholder = null;
    if(isset($field->placeholder)){
    $placeholder = $field->placeholder;
    }

    if(isset($item->{$field->name})){
        $price = $item->{$field->name};
    }
@endphp


<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group price-group">
        {!! Form::number($field->name,$price ,['class' => 'form-control price' ,'placeholder'=>$placeholder])!!}
        <label for="cloneprice" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>
</div>


@push('scripts')

@endpush
