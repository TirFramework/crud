@php
$placeholder = null;
if(isset($field->placeholder)){
    $placeholder = $field->placeholder;
}
$option = null;
if(isset($field->option)){
    $option = $field->option;
}
@endphp
<div class="form-group">
    {!! Form::label($field->name, trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::text($field->name,null,['class' => 'form-control ','placeholder'=> $placeholder , $option=> $option ])!!}
    </div>
</div>
