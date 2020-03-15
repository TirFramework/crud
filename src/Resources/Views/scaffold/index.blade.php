<?php
use Illuminate\Support\Str;
use App\Modules\Authorization\acl;
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
    <div class="panel panel-default">
        @include(config('crud.admin-panel').'::layouts.panel-heading',['name'=>$crud->name, 'actions'=>$crud->actions])
        <div class="panel-body">
            <div class="">
                <table class="table table-striped table-hover responsive nowrap" id="table" width="100%">
                    <thead>
                    <tr>
                    @foreach($crud->fields as $field)
                            @if((strpos($field->visible, 'i') !== false))
                                @if(isset($field->display))
                                    <th>{{trans("$crud->name::panel.$field->display")}}</th>
                                @else
                                    <th>{{trans("$crud->name::panel.$field->name")}}</th>
                                @endif
                            @endif
                    @endforeach
                        <th >{{trans('crud::panel.action')}}</th>

                    </tr>
                    </thead>

                    <tfoot>
                    <tr>
                        <th></th>

                    @foreach($crud->fields as $field)
                            @if((strpos($field->visible, 'i') !== false))
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
        //datetable
        <?php
            $col=null;
            $filters = null;
            $loop=0;
            $responsive= true;
            $className = null;
            foreach($crud->fields as $field):
                if(strpos($field->visible, 'i') !== false):        //check visibility for index
                    $name = $field->name;
                    $key = $crud->table.'.'.$field->name;
                    $render = null;
                    if(isset($field->datatable)):                  //relationship must have datatable field for show in datatable
                        $name = $key = $field->datatable;
                        //for many to many datatable $field->datatable must be array and have two index ,first is name and second is data
                        if(is_array($field->datatable)):
                          $name = $field->datatable[0];
                          $key =  $field->datatable[1];
                        endif;
                    endif;
                    if($field->type == 'order'):
                        $className = ",className:'ordered'";
                    endif;
                    $col .= "{ data:`$name`, name: `$key` $className, defaultContent: '' $render},";

                    //if enable drag reorder and add column must $loop = 1
                    if(strpos($field->visible, 'f') !== false){
                        if($field->type != 'relationSelect'){
                                $filters .= $loop.':'.json_encode($crud->model::select($field->name)->distinct($field->name)->pluck($field->name)).', ';
                        }
                        if($field->type == 'relationSelect'){
                            $dataModel = $field->data;
                            $dataField = 'title';
                            if(is_object($field->data)):
                                $dataModule = $dataModel = $field->data->module;
                                if(isset($field->data->model)):
                                    $dataModel = $field->data->model;
                                endif;
                                $dataField =  $field->data->field;
                            endif;
                           $class = 'App\\Modules\\'.$dataModule.'\\'.$dataModel;
                           $filters .= $loop.':'.json_encode($class::select($dataField)->distinct($dataField)->pluck($dataField)).', ';
                        }
                    }
                    $loop++;
                 endif;
             endforeach;

            ?>

        var col=[ {!! $col !!} ];
        var filters = { {!! $filters !!} };     // it must something like this         var filters = { 5:["travelogue","article","news"] };

        let table = new datatable('#table',col,"{{$crud->name}}");

        @php
            $orderField =$orderLoop=0;
        @endphp
        @foreach($crud->fields as $field)
            @if(strpos($field->visible, 'i') !== false)
                @if(strpos($field->visible, 'o') !== false )
                    @php $orderField = $orderLoop;  @endphp
                    @break
                @endif
                @php $orderLoop++; @endphp
            @endif
        @endforeach

        @if(isset($trash))
        table.create([{{$orderField}}, "desc"],filters,'trashData',{{$crud->options['datatableServerSide']}});    //([column for filter, 'desc or ace'], 'filters data','route')
        @else
        table.create([{{$orderField}}, "desc"],filters,'data',{{$crud->options['datatableServerSide']}});    //([column for filter, 'desc or ace'], 'filters data','route')
        @endif
        table.reorder();
    </script>

@endpush
