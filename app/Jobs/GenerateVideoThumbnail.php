<?php

namespace App\Jobs;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateVideoThumbnail implements ShouldQueue
{
    use Queueable;

    protected $fileNameWithExtension;
    protected $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct($fileNameWithExtension, $fileName)
    {
        $this->fileNameWithExtension = $fileNameWithExtension;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'C:\ffmpeg\bin\ffmpeg.exe',
            'ffprobe.binaries' => 'C:\ffmpeg\bin\ffprobe.exe',
            'timeout'          => 3600, // in seconds
            'ffmpeg.threads'   => 12,   // optional tuning
        ]);
        $video = $ffmpeg->open(storage_path("app/public/posts/videos/$this->fileNameWithExtension"));

        $frame = $video->frame(TimeCode::fromSeconds(1)); // snapshot at 3 seconds
        $frame->save(storage_path("app/public/thumbnails/$this->fileName.png"));
    }
}
