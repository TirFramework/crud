@php
    //set #id for jquery confilict
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


    $class = $field->data[0];
    $key = 'title';

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
