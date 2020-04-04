<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Exceptions\InternalException;
use App\Models\CrowdfundingProduct;
use App\Services\OrderService;


class OrdersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Order List')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);
    
        
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');
    
        $grid->no('订单流水号');
        
        $grid->column('user.name', 'Name');
        $grid->total_amount('total_amount')->sortable();
        $grid->paid_at('paid_at')->sortable();
        $grid->ship_status('ship_status')->display(function($value) {
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('refund_status')->display(function($value) {
            return Order::$refundStatusMap[$value];
        });
        
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->tools(function ($tools) {
            
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail(Order $order, Content $content)
    {
        return $content
            ->header('order details')
            ->body(view('admin.orders.show', ['order' => $order]));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->text('no', 'No');
        $form->number('user_id', 'User id');
        $form->textarea('address', 'Address');
        $form->decimal('total_amount', 'Total amount');
        $form->textarea('remark', 'Remark');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', 'Payment method');
        $form->text('payment_no', 'Payment no');
        $form->text('refund_status', 'Refund status')->default('pending');
        $form->text('refund_no', 'Refund no');
        $form->switch('closed', 'Closed');
        $form->switch('reviewed', 'Reviewed');
        $form->text('ship_status', 'Ship status')->default('pending');
        $form->textarea('ship_data', 'Ship data');
        $form->textarea('extra', 'Extra');

        return $form;
    }
    
    
    public function ship(Order $order, Request $request)
    {
        
        if (!$order->paid_at) {
            throw new InvalidRequestException('The order is not paid');
        }
        
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('The order has been shipped');
        }
        
        if ($order->type === Order::TYPE_CROWDFUNDING &&
            $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS) {
            throw new InvalidRequestException('Crowdfunding orders can only be shipped after successful crowdfunding');
        }
        
        $data = $this->validate($request, [
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ], [], [
            'express_company' => 'Logistics company',
            'express_no'      => 'shipment number',
        ]);
        
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            
            
            'ship_data'   => $data,
        ]);
        
        
        return redirect()->back();
    }
    
    
    public function handleRefund(Order $order, HandleRefundRequest $request, OrderService $orderService)
    {
        
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('Order status is incorrect');
        }
        
        if ($request->input('agree')) {
            
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            
            $orderService->refundOrder($order);
        } else {
            
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }
        
        return $order;
    }
    
}
