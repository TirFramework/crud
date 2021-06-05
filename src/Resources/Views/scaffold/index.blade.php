<?php
//TODO: Refactor index foreach

?>

@extends(config('crud.admin-panel').'::layouts.master')

@section('title', trans($model->getLocalization().Str::plural($model->getScaffoldName())) )

@section('page-heading')
    {{trans($model->locale.Str::plural($model->getscaffoldName()))}}

    @isset($trash)
        {{trans('crud::panel.trash')}}
     @endisset
@endsection


@section('content')
    <div id="result"></div>
    <div class="card card-default">
        @include(config('crud.admin-panel').'::layouts.panel-heading',['name'=>$model->getScaffoldName(), 'actions'=>[]])
        <div class="card-body">
            <div class="">
                <table class="table table-striped table-hover responsive nowrap" id="table" width="100%">
                    <thead>
                    <tr>
                        @foreach($model->getIndexFields() as $field)
                            <th>{{trans($model->getLocalization().$field->display)}}</th>
                        @endforeach
                        <th>
                            @lang($model->getLocalization().'action')
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

@push('scripts')
    <script>
        //TODO: Use package for pass variable to js 
        //datetable
        <?php
        $col = null;
        $filters = null;
        //if enable drag reorder and add column $loop  must be equals to 1
        $loop = 0;
        $responsive = true;
        $className = null;
        $orderField = 0;


        foreach ($model->getIndexFields() as $field):
            $name = $field->name;
            $key = $model->getTable() . '.' . $field->name;
            $render = null;
            $searchable = 'true';

            if ($field->type == 'oneToMany'):     //relationship must have datatable field for show in datatable
//                $relationModel = get_class($model->{$field->relationName}()->getModel());
//                $dataModel = new  $relationModel;
                $dataField = $field->relationName;
                $name = $key = $field->relationName . '.' . $field->relationKey;

            endif;
            //for many to many datatable $field->datatable must be array and have two index ,first is name and second is data
            if ($field->type == 'relationM'):

                $relationModel = get_class($model->model->{$field->relation[0]}()->getModel());
                $dataModel = new  $relationModel;
                $dataField = $field->relation[1];

                if (in_array($dataField, $dataModel->translatedAttributes)):
                    $name = $field->relation[0] . '[ , ].translations[].' . $field->relation[1];
                    $key = $field->relation[0] . '.translations.' . $field->relation[1];
                else:
                    $name = $field->relation[0] . '[ , ].' . $field->relation[1];
                    $key = $field->relation[0] . '.' . $field->relation[1];
                endif;


                    endif;
                    if($field->type == 'position'):
                        $className = ",className:'position'";
                        $orderField = $loop;
                    endif;

                    //add searchable item
                    if(isset($field->searchable))
                    {
                        if ($field->searchable == false || $field->searchable == 'false') {
                            $searchable = 'false';
                        }
                    }
            $col .= "{ data:`$name`, name: `$key` $className, defaultContent: '' $render, searchable: $searchable},";


            //filters
            //translated fields can not filter
//                        if(strpos($field->visible, 'f') !== false){
//                            if($field->type == 'relation' || $field->type == 'relationM'){
//
//                                $relationModel =  get_class($model->model->{$field->relation[0]}()->getModel());
//                                $dataModel = new  $relationModel;
//                                $dataField = $field->relation[1];
//
//                                //check relation model field not translated
//                                if(in_array($dataField, $dataModel->translatedAttributes) == false){
//                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($model->name))->select($dataField)->distinct($dataField)->pluck($dataField)).', ';
//                                }else{
//                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($model->name))->select('*')->get()->pluck($dataField)).', ';
//                                        if($field->type == 'relationM'){
//                                            $filters .= $loop.':["disabled in many to many translation"], ';
//                                        }
//
//                                }
//                            }else{
//                                if( in_array($field->name, $model->model->translatedAttributes) == false){
//                                    $filters .= $loop.':'.json_encode($model->model::select($field->name)->distinct($field->name)->pluck($field->name)).', ';
//                                }else{
//                                   $filters .= $loop.':'.json_encode($model->model::select('*')->get()->pluck($field->name)).', ';
//                                }
//                            }
//                         }
            $loop++;
        endforeach;


        ?>

        var col = [{!! $col !!}];
        var filters = {{!! $filters !!}};     // it must something like this         var filters = { 5:["travelogue","article","news"] };
        var dataRoute = "{{route('admin.'.$model->getScaffoldName().'.data')}}";
        var trashRoute = "{{route('admin.'.$model->getScaffoldName().'.trashData')}}";
        let table = new datatable('#table', col, "{{$model->getScaffoldName()}}");

        @if(isset($trash))
        table.create([{{$orderField}}, "desc"], filters, trashRoute, [true]);    //([column for filter, 'desc or ace'], 'filters data','route')
        @else
        table.create([{{$orderField}}, "desc"], filters, dataRoute, [true]);    //([column for filter, 'desc or ace'], 'filters data','route')
        @endif
        // table.reorder();
    </script>

@endpush
