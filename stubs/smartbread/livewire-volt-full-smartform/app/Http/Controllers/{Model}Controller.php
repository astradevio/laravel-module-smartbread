<?php

namespace Modules\{Module}\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\{Module}\Http\Requests\{Model}Request;
use Modules\{Module}\Models\{Model};

class {Model}Controller extends Controller
{
    public function index(): View
    {
        ${model}_pagination = {Model}::paginate();
        return view('{module}::{model}-index', compact('{model}_pagination'));
    }

    public function create(): View
    {
        return view('{module}::{model}-form');
    }

    public function store({Model}Request $request): RedirectResponse
    {
        {Model}::create($request->validated());
        return redirect()->route('{module}::{model}.index')->with('success', _('{Model} created successfully!'));
    }

    public function show({Model} ${model}): View
    {
        return view('{module}::{model}-form', compact('{model}'));
    }

    public function edit({Model} ${model}): View
    {
        return view('{module}::{model}-form', compact('{model}'));
    }

    public function update({Model}Request $request, {Model} ${model}): RedirectResponse
    {
        ${model}->update($request->validated());
        return redirect()->route('{module}::{model}.index')->with('success', _('{Model} updated successfully!'));
    }

    public function destroy({Model} ${model}): RedirectResponse
    {
        ${model}->delete();
        return redirect()->route('{module}::{model}.index')->with('success', _('{Model} deleted successfully!'));
    }
}
