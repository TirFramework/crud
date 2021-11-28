@extends(config('crud.admin-panel').'::layouts.master')

@section('title', trans($model->getLocalization().Str::plural($model->moduleName)) )

@section('page-heading')
    {{trans($model->locale.Str::plural($model->moduleName))}}

    @isset($trash)
        {{trans('core::panel.trash')}}
    @endisset
@endsection


@section('content')
    <div id="result"></div>
    <div class="card card-default">
        @include(config('crud.admin-panel').'::layouts.panel-heading',['name'=>$model->moduleName, 'actions'=>[]])
        <div class="card-body">
            <div class="">
                <table class="table table-striped table-hover responsive nowrap" id="table" width="100%">
                    <thead>
                    <tr>
                        @foreach($model->getIndexFields() as $field)
                            <th>{{trans($model->getLocalization().$field->display)}}</th>
                        @endforeach
                        <th>
                            @lang('core::panel.Action')
                        </th>

                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <th></th>
                        @foreach($model->getIndexFields() as $field)
                            <th></th>
                        @endforeach
                    </tr>
                    </tfoot>
                </table>

            </div>
        </div>
    </div>
@stop

@php
    $col = [];
    $filters = null;
    $loop = 0;
    $responsive = true;
    $className = null;
    $orderField = 0;
    $searchable = true;


    foreach ($model->getIndexFields() as $field):
        $name = $field->name;
        $key = $model->getTable() . '.' . $field->name;
        $render = null;

        if ($field->type == 'oneToMany'):     //relationship must have datatable field for show in datatable
            $dataField = $field->relationName;
            $name = $key = $field->relationName . '.' . $field->relationKey;
        endif;


        //for many to many datatable $field->datatable must be array and have two index ,first is name and second is data
        if ($field->type == 'manyToMany'):
            $relationModel = get_class($model->model->{$field->relation[0]}()->getModel());
            $dataModel = new  $relationModel;
            $dataField = $field->relation[1];
            $name = $field->relation[0] . '[ , ].' . $field->relation[1];
            $key = $field->relation[0] . '.' . $field->relation[1];
        endif;

        if($field->type == 'position'):
            $className = ",className:'position'";
            $orderField = $loop;
        endif;

        //add searchable item
        if ($field->searchable === false) {
            $searchable = 'false';
        }


        array_push($col, [
         'data'=>$name,
         'name'=> $key,
         'defaultContent'=> '',
         'searchable' => $searchable
        ]);


        //filters
/*       if($field->filter){
           if($field->type == 'oneToMany' || $field->type == 'manyToMany'){
               $relationModel =  get_class($model->model->{$field->relation[0]}()->getModel());
               $dataModel = new  $relationModel;
               $dataField = $field->relation[1];

               $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($model->name))->select('*')->get()->pluck($dataField)).', ';
               if($field->type == 'manyToMany'){
                   $filters .= $loop.':["disabled in many to many translation"], ';
               }
           }else{
                  $filters .= $loop.':'.json_encode($model->model::select('*')->get()->pluck($field->name)).', ';
           }
       }*/

        $loop++;



    endforeach;

    $data = [
        'moduleName' => $model->moduleName,
        'col'        => $col,
        'filters'     => '',
        'orderField' => 0,
        'dataRoute'  => route('admin.' . $model->moduleName . '.data'),
        'trashRoute' => route('admin.' . $model->moduleName . '.trashData'),
    ]
@endphp

<script type="application/json" id="service-container">
    {!! json_encode($data) !!}
</script>
