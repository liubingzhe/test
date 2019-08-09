<?php

namespace App\Admin\Controllers;

use App\Models\GiftBag;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class GiftBagController extends Controller
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
        $grid = new Grid(new GiftBag);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('name', '礼包名称');

        });
        $grid->id('Id');
        $grid->name('礼包名称');
//        $grid->is_valid('Is valid');
        $grid->include_diamonds('奖励钻石');
        $grid->include_points('奖励铜钱');
        $grid->price_diamonds('礼包钻石售价');
        $grid->price_points('礼包铜钱售价');
//        $grid->type('奖励铜钱');
        $grid->detail('礼包描述');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');

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
        $show = new Show(GiftBag::findOrFail($id));

        $show->id('Id');
        $show->name('礼包名称');
//        $show->is_valid('Is valid');
        $show->include_diamonds('奖励钻石');
        $show->include_points('奖励铜钱');
        $show->price_diamonds('礼包钻石售价');
        $show->price_points('礼包铜钱售价');
        $show->detail('礼包描述');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GiftBag);

        $form->text('name', '礼包名称');
        $form->switch('is_valid', '是否有效')->default(1);
        $form->number('include_diamonds', '奖励钻石')->default(0);
        $form->number('include_points', '奖励铜钱')->default(0);
        $form->number('price_diamonds', '礼包钻石售价')->default(0);
        $form->number('price_points', '礼包铜钱售价')->default(0);
        $form->select('gift_kind', '礼包类型')->options([1=>'签到礼包',2=>'售卖礼包',3=>'奖励礼包',4=>'兑换礼包']);
        $form->text('detail', '礼包描述');
//        $form->datetime('updated_at', 'Updated at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
