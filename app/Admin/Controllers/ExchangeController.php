<?php

namespace App\Admin\Controllers;

use App\Models\Exchange;
use App\Http\Controllers\Controller;
use App\Models\GiftBag;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ExchangeController extends Controller
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
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
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
     * @param mixed $id
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
        $grid = new Grid(new Exchange);

        $grid->id('Id');
        $grid->exchange_ma('Exchange ma');
        $grid->is_valid('Is valid');
        $grid->gift_bag_id('Gift bag id');
        $grid->begin_time('Begin time');
        $grid->end_time('End time');
        $grid->created_at('Created at');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Exchange::findOrFail($id));

//        $show->id('Id');
        $show->exchange_ma('兑换码');
        $show->is_valid('是否有效');
        $show->gift_bag_id('礼包');
        $show->begin_time('开始时间');
        $show->end_time('结束时间');
        $show->created_at('创建时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Exchange);

        $row = $this->getRandomString(10);
        $form->text('exchange_ma', '兑换码')->default($row)->readOnly();
        $form->switch('is_valid', '是否开启')->default(1);
        $form->select('gift_bag_id', '礼包')->options(function () {
            return GiftBag::pluck('name','id');
        });
        $form->datetime('begin_time', '开始时间')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_time', '结束时间')->default(date('Y-m-d H:i:s'));

        return $form;
    }
    function getRandomString($len, $chars=null)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }
}
