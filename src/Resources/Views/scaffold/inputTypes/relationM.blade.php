@php
    //set #id for jquery confilict
    $id = preg_replace("/[^a-zA-Z_]/", "", $field->name);
    $fieldName = $field->name.'[]';
    $options = ['id'=> "select-$id", 'class'=>'dropdown-toggle form-control','multiple'];




    $model = $field->data[0];
    $key = $field->data[1];

        if(isset($field->data->field)){
            $key = $field->data->field;
        }
    $values = $model::pluck($key,'id');
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
