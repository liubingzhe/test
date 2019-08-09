<?php

namespace App\Admin\Controllers;

use App\Models\Constellation;
use App\Models\Stage;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class StageController extends Controller
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
            ->header('关卡设置')
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
        $grid = new Grid(new Stage);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('name', '关卡名称');

        });
        $grid->id('Id');
        $grid->name('名称');
        $grid->icon_url('关卡图片');
        $grid->error_max('最多错误次数');
        $grid->time_len_max('最长时长');
        $grid->detail('关卡文字描述');
        $grid->constellation_id('关卡对应碎片id');
        $grid->stage_type('关卡对应碎片类型');
        $grid->images_id('关卡对应图片id');
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
        $show = new Show(Stage::findOrFail($id));

        $show->id('Id');
        $show->name('名称');
        $show->icon_url('关卡图片');
        $show->error_max('最多错误次数');
        $show->time_len_max('最长时长');
        $show->detail('关卡文字描述');
        $show->constellation_id('关卡对应碎片')->options(function(){
            return Constellation::pluck('name' , 'id');
        });
        $show->stage_type('关卡对应碎片类型');
        $show->images_id('关卡对应图片id');
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
        $form = new Form(new Stage);

        $form->text('name', '名称');
        $form->image('icon_url', '关卡图片')->default(' ');
        $form->number('error_max', '最多错误次数');
        $form->number('time_len_max', '最长时长');
        $form->number('images_id', '图片id')->default('1');
        $form->text('detail', '关卡描述');
        $form->select('stage_type', '关卡对应碎片类型')->options([1=>'正常碎片',2=>'随机碎片']);
        $form->select('constellation_id','关卡对应碎片')->options(function(){
            return Constellation::pluck('name' , 'id');
        });


        return $form;
    }
}
