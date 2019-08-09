<?php

namespace App\Admin\Controllers;

use App\Models\Device;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class DeviceController extends Controller
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
        $grid = new Grid(new Device);
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('user_id', '用户id');

        });
        $grid->id('Id');
        $grid->user_id('用户ID');
        $grid->mac('网卡MAC地址');
        $grid->device_id('设备ID');
        $grid->device_model('设备模型');
        $grid->device_name('设备名称');
        $grid->device_type('设备类型');
        $grid->device_gpu_id('显卡ID');
        $grid->device_gpu_name('显卡名称');
        $grid->device_gpu_vendor('显卡供应商');
        $grid->device_cpu_type('CPU类型');
        $grid->device_cpu_count('CPU数量');
        $grid->device_cpu_freq('CPU频率');
        $grid->device_audio_support('支持音频');
        $grid->device_mem_size('内存大小');
        $grid->device_os('设备操作系统');
        $grid->app_version('App版本');
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
        $show = new Show(Device::findOrFail($id));

        $show->id('Id');
        $show->user_id('用户ID');
        $show->mac('网卡MAC地址');
        $show->device_id('设备ID');
        $show->device_model('设备模型');
        $show->device_name('设备名称');
        $show->device_type('设备类型');
        $show->device_gpu_id('显卡ID');
        $show->device_gpu_name('显卡名称');
        $show->device_gpu_vendor('显卡供应商');
        $show->device_cpu_type('CPU类型');
        $show->device_cpu_count('CPU数量');
        $show->device_cpu_freq('CPU频率');
        $show->device_audio_support('支持音频');
        $show->device_mem_size('内存大小');
        $show->device_os('设备操作系统');
        $show->app_version('App版本');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Device);

        $form->number('user_id', '用户ID');
        $form->text('mac', '网卡MAC地址');
        $form->text('device_id', '设备ID');
        $form->text('device_model', '设备模型');
        $form->text('device_name', '设备名称');
        $form->text('device_type', '设备类型');
        $form->text('device_gpu_id', '显卡ID');
        $form->text('device_gpu_name', '显卡名称');
        $form->text('device_gpu_vendor', '显卡供应商');
        $form->text('device_cpu_type', 'CPU类型');
        $form->text('device_cpu_count', 'CPU数量');
        $form->text('device_cpu_freq', 'CPU频率');
        $form->text('device_audio_support', '支持音频');
        $form->text('device_mem_size', '你内存大小');
        $form->text('device_os', '设备操作系统');
        $form->text('app_version', 'App版本');
//        $form->datetime('updated_at', 'Updated at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
