<?php

namespace App\Repositories;

use App\Models\Slider;

class SliderRepository implements SliderRepositoryInterface
{
    public function all()
    {
        return Slider::with('product')->get();
    }

    public function create(array $data)
    {
        return Slider::create($data);
    }

    public function findById($id)
    {
        return Slider::with('product')->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $slider = Slider::findOrFail($id);
        $slider->update($data);
        return $slider->load('product');
    }

    public function delete($id)
    {
        return Slider::destroy($id);
    }
}
