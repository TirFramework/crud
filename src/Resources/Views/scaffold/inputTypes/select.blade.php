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
?>
<div class="form-group">
    {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::select($fieldName, $field->data, null,$options)!!}
    </div>
</div>
