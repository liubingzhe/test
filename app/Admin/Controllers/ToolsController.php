<?php

namespace App\Admin\Controllers;

use App\Models\Tools;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ToolsController extends Controller
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
        $grid = new Grid(new Tools);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('tools_name', '道具名称');

        });
        $grid->id('Id');
//        $grid->tools_type('Tools type');
        $grid->tools_name('道具名称');
//        $grid->is_valid('Is valid');
        $grid->add_vitality('增加活力值');
        $grid->add_diamonds('增加钻石');
        $grid->add_points('增加铜钱');
        $grid->price_diamonds('钻石价格');
        $grid->price_points('铜钱价格');
        $grid->icon_path('道具图片地址');
        $grid->detail('道具描述');
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
        $show = new Show(Tools::findOrFail($id));

        $show->id('Id');
        $show->tools_name('道具名称');
//        $show->is_valid('Is valid');
        $show->add_vitality('增加活力值');
        $show->add_diamonds('增加钻石');
        $show->add_points('增加铜钱');
        $show->price_diamonds('钻石价格');
        $show->price_points('铜钱价格');
        $show->icon_path('道具图片地址');
        $show->detail('道具描述');
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
        $form = new Form(new Tools);

        $form->switch('tools_type', '道具类型');
        $form->text('tools_name', '道具名称');
        $form->switch('is_valid', '道具是否有效')->default(1);
        $form->text('add_vitality', '增加活力值');
        $form->number('add_diamonds', '增加钻石');
        $form->number('add_points', '增加铜钱');
        $form->number('price_diamonds', '钻石价格');
        $form->number('price_points', '铜钱价格');
        $form->image('icon_path', '道具图片地址');
        $form->textarea('detail', '道具详情');
//        $form->datetime('updated_at', 'Updated at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
