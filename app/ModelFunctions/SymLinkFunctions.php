<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use App\Configs;
use App\Photo;
use App\SymLink;
use Illuminate\Support\Facades\Storage;

class SymLinkFunctions
{
    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    /**
     * AlbumFunctions constructor.
     */
    public function __construct(SessionFunctions $sessionFunctions)
    {
        $this->sessionFunctions = $sessionFunctions;
    }

    public function find(Photo $photo): ?SymLink
    {
        if (Storage::getDefaultDriver() === 's3') {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        if (Configs::get_value('SL_enable', '0') === '0') {
            return null;
        }

        if ($this->sessionFunctions->is_admin() && Configs::get_value('SL_for_admin', '0') === '0') {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $sym = SymLink::where('photo_id', $photo->id)
            ->orderBy('created_at', 'DESC')
            ->first();
        if ($sym === null) {
            $sym = new SymLink();
            $sym->set($photo);
            $sym->save();
        }

        return $sym;
    }

    /**
     * get URLS of pictures.
     *
     * @param array<mixed> $return
     */
    public function getUrl(Photo $photo, array &$return): void
    {
        $sym = $this->find($photo);
        if ($sym !== null) {
            $sym->override($return);
        }
    }

    /**
     * Clear the table of existing SymLinks.
     *
     * @throws \Exception
     */
    public function clearSymLink(): string
    {
        $symlinks = SymLink::all();
        $no_error = true;
        foreach ($symlinks as $symlink) {
            $no_error &= $symlink->delete();
        }

        return $no_error ? 'true' : 'false';
    }

    /**
     * Remove outdated SymLinks.
     */
    public function remove_outdated(): bool
    {
        $symlinks = SymLink::where(
            'created_at',
            '<',
            \now()->subDays(\intval(Configs::get_value('SL_life_time_days', '3')))->toDateTimeString()
        )->get();
        $success = true;
        foreach ($symlinks as $symlink) {
            // it may be faster to just do the unlink and then one query for all the delete.
            $success &= $symlink->delete();
        }

        return $success;
    }
}
