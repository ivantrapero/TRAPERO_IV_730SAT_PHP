<?php

namespace App\Http\Controllers;

use App\Mail\JobPosted;
use App\Models\Job;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::with('employer')->latest()->simplePaginate(3);

        return view('jobs.index', [
            'jobs' => $jobs
        ]);
    }

    public function create()
    {
        return view('jobs.create');
    }

    public function show(Job $job)
    {
        return view('jobs.show', ['job' => $job]);
    }

    public function store()
    {
        request()->validate([
            'title' => ['required', 'min:3'],
            'salary' => ['required']
        ]);

        $job = Job::create([
            'title' => request('title'),
            'salary' => request('salary'),
            'employer_id' => 1, // Adjust this as necessary for your application
        ]);

        // Check if the employer and user exist before sending the email
        if ($job->employer && $job->employer->user) {
            Mail::to($job->employer->user)->queue(new JobPosted($job));
        } else {
            // Log an error or handle the case where employer or user does not exist
            \Log::warning('Job posted but employer or user does not exist', [
                'job_id' => $job->id,
                'employer_id' => $job->employer_id
            ]);
        }

        return redirect('/jobs')->with('success', 'Job created successfully!');
    }

    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job]);
    }

    public function update(Job $job)
    {
        Gate::authorize('edit-job', $job);

        request()->validate([
            'title' => ['required', 'min:3'],
            'salary' => ['required']
        ]);

        $job->update([
            'title' => request('title'),
            'salary' => request('salary'),
        ]);

        return redirect('/jobs/' . $job->id)->with('success', 'Job updated successfully!');
    }

    public function destroy(Job $job)
    {
        Gate::authorize('edit-job', $job);

        $job->delete();

        return redirect('/jobs')->with('success', 'Job deleted successfully!');
    }
}
