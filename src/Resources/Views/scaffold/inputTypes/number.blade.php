<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {!! Form::number($field->name,null,['class' => 'form-control'])!!}
        {!! Form::label($field->name, trans("$crud->name::panel.$field->display"), ['class' => 'control-label']) !!}
    </div>
</div>
