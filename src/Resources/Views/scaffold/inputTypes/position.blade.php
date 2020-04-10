@php
    $sortOrder = $crud->model::select('position')->max('position') + 1;
    if(isset($item->sort_order)):
         $sortOrder = $item->sort_order;
    endif;
@endphp
<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {!! Form::number($field->name,$sortOrder,['class' => 'form-control'])!!}
        {!! Form::label($field->name, trans("$crud->name::panel.$field->name"), ['class' => 'control-label']) !!}
    </div>
</div>
