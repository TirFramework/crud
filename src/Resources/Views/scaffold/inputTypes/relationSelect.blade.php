@php
/*
    $id = preg_replace("/[^a-zA-Z_]/", "", $field->name);
    $fieldName = $field->name;
    $options = ['id'=> "select-$id", 'class'=>'dropdown-toggle form-control'];
    if(isset($field->multiple)):
        if( $field->multiple == true):
            $options['multiple'] = 'multiple';
            $fieldName = $field->name.'[]';
        endif;
    else:
        $options ['placeholder'] = trans('$crud->name::panel.select').' '.trans("$crud->name::panel.$field->display");
    endif;


    $model = $module = $field->data;
    $key = 'title';

    //use is object becouse we converted array of fields in model, to object
    //so we use array like object
    if(is_object($field->data)){
        if(isset($field->data->module)){
            //first we set model = module for when module name and model had same name
            $model = $module = $field->data->module;
        }
        if(isset($field->data->model)){
            //if model set we get model name from data when module and model did'nt have same name
            $model = $field->data->model;
        }
        if(isset($field->data->field)){
            $key = $field->data->field;
        }
    }
    $loadModel = 'App\Modules\\'.$module.'\\'.$model;
    $values = $loadModel::pluck($key,'id');
@endphp

<div class="form-group">
    {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::select($fieldName, $values, null,$options)!!}
    </div>
</div>

@push('scripts')
    <script>
    $("#select-{{$id}}").select2({
        placeholder: "{{trans('crud::panel.select').' '.trans("$crud->name::panel.$field->display")}}",
        // ajax: {
        //     url: '/admin/{{strtolower($module)}}/select/?key={{$key}}',
        //     dataType: 'json',
        //     data: function (params) {
        //         var query = {
        //             search: params.term
        //         }
        //         return query;
        //     }
        // }
    });
</script>
@endpush
