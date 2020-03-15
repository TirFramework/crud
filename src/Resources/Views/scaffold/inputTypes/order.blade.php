@php
    $ordered = $crud->model::select('ordered')->max('ordered') + 1;
    if(isset($item->ordered)):
         $ordered = $item->ordered;
    endif;
@endphp

<div class="form-group">
    {!! Form::label($field->name, trans("$crud->name::panel.$field->name"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::number($field->name,$ordered,['class' => 'form-control'])!!}
    </div>
</div>
