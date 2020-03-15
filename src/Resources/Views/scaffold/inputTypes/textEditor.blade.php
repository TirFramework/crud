<div class="form-group">
    {!! Form::label($field->name, trans("$crud->name::panel.$field->name"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::textarea($field->name,null,['class' => 'form-control textarea', 'id'=>'editor'])!!}
    </div>
</div>
