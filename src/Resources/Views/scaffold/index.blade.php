<?php
//TODO: Refactor index foreach

use Illuminate\Support\Str;
?>

@extends(config('crud.admin-panel').'::layouts.master')

@section('title', trans("$crud->name::panel.".Str::plural($crud->name)) )

@section('page-heading')
    {{trans("$crud->name::panel.".Str::plural($crud->name))}}
    @isset($trash)
        {{trans('crud::panel.trash')}}
     @endisset
@endsection
@section('content')
    <div id="result"></div>
    <div class="card card-default">
        @include(config('crud.admin-panel').'::layouts.panel-heading',['name'=>$crud->name, 'actions'=>$crud->actions])
        <div class="card-body">
            <div class="">
                <table class="table table-striped table-hover responsive nowrap" id="table" width="100%">
                    <thead>
                    <tr>
                    @foreach($crud->fields as $group)
                        @foreach($group->tabs as $tab)
                            @foreach($tab->fields as $field)
                                @if((strpos($field->visible, 'i') !== false))
                                    @if(isset($field->display))
                                        <th>{{trans("$crud->name::panel.$field->display")}}</th>
                                    @else
                                        <th>{{trans("$crud->name::panel.$field->name")}}</th>
                                    @endif
                                @endif
                            @endforeach
                        @endforeach
                    @endforeach
                        <th >
                            @lang("$crud->name::panel.action")
                        </th>

                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <th></th>
                        @foreach($crud->fields as $group)
                            @foreach($group->tabs as $tab)
                                @foreach($tab->fields as $field)
                                    @if((strpos($field->visible, 'i') !== false))
                                        <th></th>
                                    @endif
                                @endforeach
                            @endforeach
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

            foreach($crud->fields as $group):
                foreach($group->tabs as $tab):
                    foreach($tab->fields as $field):
                if(strpos($field->visible, 'i') !== false):        //check visibility for index
                    $name = $field->name;
                    $key = $crud->table.'.'.$field->name;
                    $render = null;
                    $searchable = 'true';
                    //if feild is translation for use in data table we must do like many to many relation
                    if(in_array($field->name, $crud->model->translatedAttributes)):
                        $name = 'translations'. '[].'. $field->name;
                        $key =  'translations'. '.'. $field->name;
                    endif;

                    if($field->type =='relation'):     //relationship must have datatable field for show in datatable
                        $relationModel =  get_class($crud->model->{$field->relation[0]}()->getModel());
                        $dataModel = new  $relationModel;
                        $dataField = $field->relation[1];

                        if(in_array($dataField, $dataModel->translatedAttributes)):
                            $name = $field->relation[0].'.translations[].'. $field->relation[1];
                            $key = $field->relation[0].'.translations.'. $field->relation[1];
                        else:
                            $name = $key = $field->relation[0].'.'.$field->relation[1];
                        endif;
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
                        if(strpos($field->visible, 'f') !== false){
                            if($field->type == 'relation' || $field->type == 'relationM'){

                                $relationModel =  get_class($crud->model->{$field->relation[0]}()->getModel());
                                $dataModel = new  $relationModel;
                                $dataField = $field->relation[1];

                                //check relation model field not translated
                                if(in_array($dataField, $dataModel->translatedAttributes) == false){
                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($crud->name))->select($dataField)->distinct($dataField)->pluck($dataField)).', ';
                                }else{
                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($crud->name))->select('*')->get()->pluck($dataField)).', ';
                                        if($field->type == 'relationM'){
                                            $filters .= $loop.':["disabled in many to many translation"], ';
                                        }

                                }
                            }else{
                                if( in_array($field->name, $crud->model->translatedAttributes) == false){
                                    $filters .= $loop.':'.json_encode($crud->model::select($field->name)->distinct($field->name)->pluck($field->name)).', ';
                                }else{
                                   $filters .= $loop.':'.json_encode($crud->model::select('*')->get()->pluck($field->name)).', ';
                                }
                            }
                         }
                    $loop++;
                 endif;
             endforeach;
                endforeach;
            endforeach;

            ?>

        var col=[ {!! $col !!} ];
        var filters = { {!! $filters !!} };     // it must something like this         var filters = { 5:["travelogue","article","news"] };
        var  dataRoute = "{{route($crud->routeName.'.data')}}";
        var  trashRoute = "{{route($crud->routeName.'.trashData')}}";
        let table = new datatable('#table',col,"{{$crud->name}}");

        @foreach($crud->fields as $group)
            @foreach($group->tabs as $tab)
                @foreach($tab->fields as $field)
                    @if(strpos($field->visible, 'i') !== false)
                        @if(strpos($field->visible, 'o') !== false )
                            @php $orderField = $loop->index;  @endphp
                            @break
                        @endif
                    @endif
                @endforeach
            @endforeach
        @endforeach

        @if(isset($trash))
        table.create([{{$orderField}}, "desc"],filters,trashRoute,{{$crud->options['datatableServerSide']}});    //([column for filter, 'desc or ace'], 'filters data','route')
        @else
        table.create([{{$orderField}}, "desc"],filters,dataRoute,{{$crud->options['datatableServerSide']}});    //([column for filter, 'desc or ace'], 'filters data','route')
        @endif
        table.reorder();
    </script>

@endpush
