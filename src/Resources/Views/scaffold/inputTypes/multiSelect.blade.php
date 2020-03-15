<div class="form-group">
    {!! Form::label($field->name,trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::select($field->name.'[]', $field->data, null,
        ['title' => trans('panel.select').' '.trans("$crud->name::panel.$field->display"),
         'class'=>'select2 input-lg dropdown-toggle form-control',
         'data-live-search="true" multiple',
        ])!!}
    </div>
</div>
