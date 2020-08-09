@extends('first-panel::layouts.master')
@section('navbar')
{{--navbar add here--}}
@endsection

@php
    use Illuminate\Support\Arr;
@endphp

@section('title', trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')

@php 
$tabStatus = Session::get('tab') ?? null @endphp

{!! Form::model($item, ['route'=>["$crud->name.update",$item->getKey()],'method' => 'put',
        'class'=>'form-horizontal ', 'enctype'=>'multipart/form-data']) !!}

        {{-- For active tab --}}
        {!! Form::hidden('tab', $tabStatus , ['id' => 'tab']) !!}

        


            <div class="row">
                {{--Side--}}
                <div class="col-md-3 col-12">

                    <div id="accordion">
                    @foreach($crud->fields as $group)
                    @php
                        $loopIndex = $loop->index
                    @endphp
                        @if($group->type == 'group')
                            <div class="card">
                            <div class="card-header" id="heading-{{$group->name}}">
                                <h5 class="mb-0">
                                    <a class="btn btn-link" data-toggle="collapse" data-target="#collapse-{{$group->name}}"
                                       aria-expanded="true" aria-controls="collapse-{{$group->name}}">
                                        @lang("$crud->name::panel.$group->name")

                                    </a>
                                </h5>
                            </div>

                            

                            <div id="collapse-{{$group->name}}" class="collapse 


                                @foreach($group->tabs as $tab )

                                @if ($tabStatus == null )
                                        @if ( $loop->first && $loopIndex == 0 )
                                            show active
                                        @endif
                                @else
                                    @if( $tabStatus == "#v-pills-$tab->name" ) show @endif 
                                @endif

                                @endforeach



                                " aria-labelledby="heading-{{$group->name}}" data-parent="#accordion">
                                <div class="card-body">
                                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">

                                        {{--Other tabs--}}
                                        @foreach($group->tabs as $tab )
                                            @if($tab->type == 'tab')
                                                <a class="nav-link 
                                                
                                                @if ($tabStatus == null )
                                                    @if ( $loop->first && $loopIndex == 0 )
                                                        show active
                                                    @endif
                                                @else
                                                    @if( $tabStatus == "#v-pills-$tab->name" ) active @endif 
                                                @endif
        
                                                "
                                                   href="#v-pills-{{$tab->name}}" >
                                                   @lang("$crud->name::panel.$tab->name")
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                        @endif
                    @endforeach
                    </div>
                </div>

                {{--Body--}}
                <div class="col-md-9 col-12">

                    <div class="card card-default">
                        <div class="card-header d-flex align-items-center">
                             {{--TODO: //add tab title here--}}
                            {{--Submit & Cancel--}}

                            @if(view()->exists("$crud->name::admin.inputTypes.update"))
                                @include("$crud->name::admin.inputTypes.update",['crud'=>$crud])
                            @elseif (view()->exists("admin.$crud->name.inputTypes.update"))
                                @include("admin.$crud->name.inputTypes.update",['crud'=>$crud])
                            @else    
                                @include("crud::scaffold.inputTypes.update",['crud'=>$crud])
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                            @foreach($crud->fields as $group)

                            @php
                                $loopIndex = $loop->index
                            @endphp
                            
                                    @foreach($group->tabs as $tab)

                                   
                                    
                                        <div class="tab-pane fade
                                        @if ($tabStatus == null )
                                        @if ( $loop->first && $loopIndex == 0 )
                                            show active
                                        @endif
                                        @else
                                        @if( $tabStatus == "#v-pills-$tab->name" ) show active @endif 
                                        @endif
                                        " id="v-pills-{{$tab->name}}">

                                            <h4>  @lang("$crud->name::panel.$tab->name")  </h4>

                                            <div class="row">
                                                @foreach($tab->fields as $field)
                                                    @if(strpos($field->visible, 'e') !== false)
                                                        @if(!isset($field->display))
                                                            @php $field->display = $field->name; @endphp
                                                        @endif
                                                        {{--check local folder have input or no--}}
                                                        @if(view()->exists("admin.$crud->name.inputTypes.$field->type"))
                                                            @include("admin.$crud->name.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                                                        @elseif (view()->exists("$crud->name::admin.inputTypes.$field->type"))
                                                            @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                                                        @else
                                                            @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>

            </div>

{!! Form::close() !!}


@endsection


@push('scripts')
<script>
    editor();



    $(".form-horizontal").validate({
        lang: $('html').attr('lang'),
        ignore: [],
        onkeyup: true,
        onfocus: true
    });


    $('.nav-link').click(function (event) {
        
        event.preventDefault()        
        var id = $(this).attr('href');
        activeingTab(id);

        $('#tab').val(id);

    });


    function activeingTab(element) {
        $('.nav-link').removeClass('active');
        $('a[href="'+element+'"]').addClass('active');
        $('.tab-content .tab-pane').removeClass('active show');
        $(element).addClass('active show');
    }

    // var id = location.hash;
    // var id = $('.nav-link').first().attr('href');
    // activeingTab({{ $tabStatus }});

</script>
@endpush
