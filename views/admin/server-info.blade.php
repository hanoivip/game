@extends('hanoivip::admin.layouts.admin')

@section('title', 'Danh sach may chu')

@section('content')
<section class="content-header">
  <h4>
   Danh sách máy chủ
  </h4>
</section>
 <section class="content">
    <div class="row">
    	<div class="col-sm-12">
    		
    		<table id="example2" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                <thead>
	                <tr role="row"> 
	                	<td class="text-center">id</td>
						<td class="text-center">name</td>
						<td class="text-center">ident</td>
						<td class="text-center">title</td>
						<td class="text-center">description</td>
						<td class="text-center">login_uri</td>
						<td class="text-center">recharge_uri</td>
						<td class="text-center">operate_uri</td>
						<td class="text-center">created_at</td>
						<td class="text-center">delelte</td>
	                </tr>
                </thead>
                <tbody>     
                	@if (empty($servers))
						(( Chua co sv nao ))
					@else
						@foreach ($servers as $server)
    		                <tr role="row" class="odd">
    		                  	<td class="text-center">{{ $server->id }}</td>
    							<td class="text-center">{{ $server->name }}</td>
    							<td class="text-center">{{ $server->ident }}</td>
    							<td class="text-center">{{ $server->title }}</td>
    							<td class="text-center">{{ $server->description }}</td>
    							<td class="text-center">{{ $server->login_uri }}</td>
    							<td class="text-center">{{ $server->recharge_uri }}</td>
    							<td class="text-center">{{ $server->operate_uri }}</td>
    							<td class="text-center">{{ $server->created_at }}</td>
    							<td class="text-center">
    								<form method="POST" action="{{ route('ecmin.server.remove') }}">
    								{{ csrf_field() }}
    									<input id="ident" name="ident" type="hidden" value="{{$server->ident}}">
    									<button type="submit" class="btn btn-primary">Delete</button>
    								</form>
    							</td>
    		                </tr>
    	                @endforeach
				@endif
	            </tbody>
            </table>



<form method="POST" action="{{ route('ecmin.server.add') }}">
{{ csrf_field() }}
	Server name (s1, s2,...) (khong trung lap)
	<input id="name" name="name" type="text">
	Server ident (1, 2, 3..) (khong trung lap)
	<input id="ident" name="ident" type="text">
	Ten hien thi
	<input id="title" name="title" type="text">
	Mieu ta
	<input id="description" name="description" type="text">
	Login Uri
	<input id="login_uri" name="login_uri" type="text">
	Recharge Uri
	<input id="recharge_uri" name="recharge_uri" type="text">
	Operate Uri
	<input id="operate_uri" name="operate_uri" type="text">
	
	<button type="submit" class="btn btn-primary">Add</button>
</form>

<form method="GET" action="{{ route('user-find') }}">
{{ csrf_field() }}
	<button type="submit" class="btn btn-primary">Back</button>
</form>
	
</div>
</div>
</section>
@endsection