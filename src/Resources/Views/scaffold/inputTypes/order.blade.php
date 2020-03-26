@php
    $sortOrder = $crud->model::select('sort_order')->max('sort_order') + 1;
    if(isset($item->sort_order)):
         $sortOrder = $item->sort_order;
    endif;
@endphp

<div class="form-group">
    {!! Form::label($field->name, trans("$crud->name::panel.$field->name"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::number($field->name,$sortOrder,['class' => 'form-control'])!!}
    </div>
</div>
