<?php
$fieldName = $field->name;
$options = ['class'=>'select2 input-lg dropdown-toggle form-control'];
if(isset($field->multiple)):
    if( $field->multiple == true):
        $options['multiple'] = 'multiple';
        $fieldName = $field->name.'[]';
    endif;
else:
    $options ['placeholder'] = trans('crud::panel.select').' '.trans("$crud->name::panel.$field->display");
endif;


$model = null;
if( isset($item->{$field->name}) ){
$model = $item->{$field->name};
}
?>

<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {{-- {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => ' control-label']) !!}
        <div class="">
            {!! Form::select($fieldName, $field->data, null,$options)!!}
        </div> --}}


        <select class="form-control
             @error( $field->name ) is-invalid @enderror"
                id="{{$field->name}}"
                name="{{$field->name}}"
            >

            @isset( $field->placeholder )
                <option selected="selected" value="">
                    {{$field->placeholder}}
                </option>
            @endisset

            @foreach ($field->data as $key => $option)
            <option
            @if(
            old($field->name, $model ) == $key
            ) {{ 'selected' }}
                @endif
                value="{{$key}}">{{$option}}</option>
            @endforeach
        </select>

        <label for="{{$field->name}}" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>
</div>


@push('scripts')
    <script>
        $("#{{$field->name}}").select2({
            placeholder: "{{$field->placeholder ?? null}}",
            dir: $('body').attr('dir'),
        });
    </script>
@endpush
