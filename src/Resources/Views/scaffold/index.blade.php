<?php
//TODO: Refactor index foreach

use Illuminate\Support\Str;
use Tir\Crud\Support\Scaffold\Crud;
?>

@extends(config('crud.admin-panel').'::layouts.master')

@section('title', trans($crud->locale.Str::plural($crud->name)) )

@section('page-heading')
    {{trans($crud->locale.Str::plural($crud->name))}}

    @isset($trash)
        {{trans('crud::panel.trash')}}
     @endisset
@endsection


@section('content')
    <div id="result"></div>
    <div class="card card-default">
        @include(config('crud.admin-panel').'::layouts.panel-heading',['name'=>$crud->name, 'actions'=>[]])
        <div class="card-body">
            <div class="">
                <table class="table table-striped table-hover responsive nowrap" id="table" width="100%">
                    <thead>
                    <tr>
                        @foreach($crud->indexFields as $field)
                            @if($field->showOnIndex)
                                <th>{{trans($crud->locale.$field->display)}}</th>
                            @endif
                        @endforeach
                        <th >
                            @lang($crud->locale.'action')
                        </th>

                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <th></th>
                        @foreach($crud->indexFields as $field)
                            @if(($field->showOnIndex))
                                <th></th>
                            @endif
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
            $col=null;
            $filters = null;
            //if enable drag reorder and add column $loop  must be equals to 1
            $loop=0;
            $responsive= true;
            $className = null;
            $orderField =0;


            foreach($crud->indexFields as $field):
                    $name = $field->name;
                    $key = $crud->table.'.'.$field->name;
                    $render = null;
                    $searchable = 'true';
                    //if field is translation for use in data table we must do like many to many relation

//                    if(in_array($field->name, $crud->getModel()->translatedAttributes)):
//                        $name = 'translations'. '[].'. $field->name;
//                        $key =  'translations'. '.'. $field->name;
//                    endif;

                    if($field->type =='oneToMany'):     //relationship must have datatable field for show in datatable
                        $relationModel =  get_class($crud->model->{$field->relationName}()->getModel());
                        $dataModel = new  $relationModel;
                        $dataField = $field->relationName;

//                        if(in_array($dataField, $dataModel->translatedAttributes)):
//                            $name = $field->relation[0].'.translations[].'. $field->relation[1];
//                            $key = $field->relation[0].'.translations.'. $field->relation[1];
//                        else:
                            $name = $key = $field->relationName.'.'.$field->relationKey;
//                        endif;
                    endif;
                    //for many to many datatable $field->datatable must be array and have two index ,first is name and second is data
                    if($field->type  == 'relationM'):

                        $relationModel =  get_class($crud->model->{$field->relation[0]}()->getModel());
                        $dataModel = new  $relationModel;
                        $dataField = $field->relation[1];

                        if(in_array($dataField, $dataModel->translatedAttributes)):
                            $name = $field->relation[0]. '[ , ].translations[].'. $field->relation[1];
                            $key  = $field->relation[0]. '.translations.' . $field->relation[1];
                        else:
                            $name = $field->relation[0]. '[ , ].'. $field->relation[1];
                            $key =  $field->relation[0]. '.'. $field->relation[1];
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
//                                $relationModel =  get_class($crud->model->{$field->relation[0]}()->getModel());
//                                $dataModel = new  $relationModel;
//                                $dataField = $field->relation[1];
//
//                                //check relation model field not translated
//                                if(in_array($dataField, $dataModel->translatedAttributes) == false){
//                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($crud->name))->select($dataField)->distinct($dataField)->pluck($dataField)).', ';
//                                }else{
//                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($crud->name))->select('*')->get()->pluck($dataField)).', ';
//                                        if($field->type == 'relationM'){
//                                            $filters .= $loop.':["disabled in many to many translation"], ';
//                                        }
//
//                                }
//                            }else{
//                                if( in_array($field->name, $crud->model->translatedAttributes) == false){
//                                    $filters .= $loop.':'.json_encode($crud->model::select($field->name)->distinct($field->name)->pluck($field->name)).', ';
//                                }else{
//                                   $filters .= $loop.':'.json_encode($crud->model::select('*')->get()->pluck($field->name)).', ';
//                                }
//                            }
//                         }
                    $loop++;
             endforeach;


            ?>

        var col=[ {!! $col !!} ];
        var filters = { {!! $filters !!} };     // it must something like this         var filters = { 5:["travelogue","article","news"] };
        var  dataRoute = "{{route($crud->routeName.'.data')}}";
        var  trashRoute = "{{route($crud->routeName.'.trashData')}}";
        let table = new datatable('#table',col,"{{$crud->name}}");

{{--        @foreach($crud->fields as $group)--}}
{{--            @foreach($group->tabs as $tab)--}}
{{--                @foreach($tab->fields as $field)--}}
{{--                    @if(strpos($field->visible, 'i') !== false)--}}
{{--                        @if(strpos($field->visible, 'o') !== false )--}}
{{--                            @php $orderField = $loop->index;  @endphp--}}
{{--                            @break--}}
{{--                        @endif--}}
{{--                    @endif--}}
{{--                @endforeach--}}
{{--            @endforeach--}}
{{--        @endforeach--}}

        @if(isset($trash))
        table.create([{{$orderField}}, "desc"],filters,trashRoute,[true]);    //([column for filter, 'desc or ace'], 'filters data','route')
        @else
        table.create([{{$orderField}}, "desc"],filters,dataRoute,[true]);    //([column for filter, 'desc or ace'], 'filters data','route')
        @endif
        table.reorder();
    </script>

@endpush
