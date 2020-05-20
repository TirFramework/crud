@php

$name = $field->name;

// remove '[' and ']' from field name because fix upload problem
$index = preg_replace('/[\[\]]/i', '_' , $field->name);
@endphp

<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        <div class="input-group">
            <span class="input-group-btn">
                <a id="{{$index}}" data-input="{{$index}}_input" data-preview="{{$index}}_holder" class="image-btn btn btn-primary">
                    <i class="fas fa-image"></i> {{trans('crud::panel.choose')}}
                </a>
            </span>
            {!! Form::text($field->name,null,['class' => 'form-control','id'=>$index.'_input', 'placeholder'=> trans("$crud->name::panel.$field->name")])!!}
        </div>
        {!! Form::label("$field->name", trans("$crud->name::panel.$field->display"), ['class' => 'control-label uploder-label']) !!}
{{--        <img id="{{$index}}_holder" @isset($image) src="{{url('/').'/'.$image}}" @endisset class="image-holder">--}}

        <div class="image-holder" id="{{$index}}_holder">
            @isset($item->{$name})
                <img src="{{ $item->{$name} }}" alt="" style="max-width: 100px; max-height: 100px">
            @endisset
        </div>

    </div>
</div>


@push('scripts')
    <script>
        $('#{{$index}}').filemanager('image');   //btn image
    </script>
@endpush