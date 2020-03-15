<div class="form-group">
    {!! Form::label($field->name, trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::number($field->name,null,['class' => 'form-control price'])!!}
    </div>
</div>
