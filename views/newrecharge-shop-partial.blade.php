@if (!empty($items))
    <select id="item" name="item">
    	<option value="">Choose item to buy</option>
    	@foreach ($items as $item)
    		<option value="{{$item->merchant_id}}">
    			{{$item->merchant_title}} - {{$item->price}} {{$item->currency}}
    		</option>
    	@endforeach
    </select>
@else
	<p>{{__('hanoivip.game::newrecharge.shop-empty')}}</p>
@endif