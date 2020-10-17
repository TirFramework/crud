@php
$model = null;
if( isset($item->{$field->name}) ){
    $model = $item->{$field->name};
}
$id = $field->name;
$class = 'form-control';
$options = ['class'=>$class , $field->validation ?? null, $field->option ?? null];

if(isset($field->placeholder)){
    $options['placeholder'] = $field->placeholder;
}

if($errors->has($field->name)){
    $options['class'] = $class. ' is-invalid';
}
@endphp

<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {!!  Form::text($field->name,null,$options)  !!}

            <span class="invalid-feedback" role="alert">
                @error($field->name)
                <strong>{{ $message }}</strong>
                @enderror
            </span>
            <label for="{{$field->name}}" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>

</div>
