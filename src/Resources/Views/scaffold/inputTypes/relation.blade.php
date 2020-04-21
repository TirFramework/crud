@php
    //set #id for jquery confilict
    $id = preg_replace("/[^a-zA-Z_]/", "", $field->name);
    $fieldName = $field->name;
    $options = ['id'=> "select-$id", 'class'=>'dropdown-toggle form-control'];

    //when we are in create page set $item to null for undefind variable error
    if(!isset($item)){
        $item = null;
    }

    $options ['placeholder'] = trans('$crud->name::panel.select').' '.trans("$crud->name::panel.$field->display");

     $model =   get_class($crud->model->{$field->relation[0]}()->getModel());
    $key = $field->relation[1];
    $model = new $model;

    if (in_array($key, $model->translatedAttributes)){
        $values = $model::select('*')->get()->pluck($key,'id');
    }else{
        $values = $model::select($key,'id')->where($key,$item->{$field->name})->pluck($key,'id');
    }


@endphp

<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {!! Form::select($fieldName, $values, null,$options)!!}
        {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => 'control-label']) !!}
    </div>
</div>

@push('scripts')
    <script>
    $("#select-{{$id}}").select2({
        placeholder: "{{trans('crud::panel.select').' '.trans("$crud->name::panel.$field->display")}}",
        dir: $('body').attr('dir'),
        ajax: {
            url: "/admin/{{$model::$routeName}}/select/?key={{$key}}",
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    page: params.page || 0
                }
                return query;
            }
        }
    });
</script>
@endpush
