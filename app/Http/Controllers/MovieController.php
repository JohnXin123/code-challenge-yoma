<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\MovieResource;
use App\Models\Author;

class MovieController extends Controller
{
    public function getMovieLists(Request $request)
    {
        $movies = Movie::paginate($request->paginate);

        $final_results =  MovieResource::collection($movies);

        return response()->success($final_results,"Success",200);
    }

    public function getMovieDetails($movie_id)
    {
        $currentMovie = Movie::with('author', 'genres', 'tags','comments')->findOrFail($movie_id);

        // Get the related movies based on author, genres, tags, and IMDb rating
        $relatedMovies = Movie::where('id', '<>', $currentMovie->id)
            ->where(function ($query) use ($currentMovie) {
                $query->whereHas('author', function ($query) use ($currentMovie) {
                    $query->where('author_id', $currentMovie->author_id);
                })->orWhereHas('genres', function ($query) use ($currentMovie) {
                    $query->whereIn('genre_id', $currentMovie->genres->pluck('id'));
                })->orWhereHas('tags', function ($query) use ($currentMovie) {
                    $query->whereIn('tag_id', $currentMovie->tags->pluck('id'));
                })->orWhere('imdb_rating', '>=', $currentMovie->imdb_rating);
            })
            ->limit(7)
            ->select('id', 'title')
            ->get();

        $data['currentMovie'] = new MovieResource($currentMovie);
        $data['relatedMovies'] = $relatedMovies;

        return response()->success($data,"Success",200);
    }

    public function createMovie()
    {
        $data['genres'] = Genre::select('id','name')->get();

        $data['tags'] = Tag::select('id','name')->get();

        $data['authors'] = Author::select('id','name')->get();

        return response()->success($data,"Success",200);
    }

    public function storeMovie(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:30',
            'summary' => 'required|min:10',
            'coverImage' => 'required|mimes:png,jpg',
            'imdbRating' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'pdfLink' => 'required',
            'authorId' => 'required|integer|exists:authors,id',
            'tags' => 'required|array',
            'tags.*' => 'required',
            'genres' => 'required|array',
            'genres.*' => 'required',
        ]);

        $photoPath = file_get_contents($request->coverImage);
        $photoData = base64_encode($photoPath);

        try {

            DB::beginTransaction();

            $movie = Movie::create([
                'title' => $request->title,
                'summary' => $request->summary,
                'cover_image' => $photoData,
                'imdb_rating' => $request->imdbRating,
                'pdf_link' => $request->pdfLink,
                'author_id' => $request->authorId,
            ]);

            $movie->tags()->attach($request->tags);
            $movie->genres()->attach($request->genres);

            DB::commit();


        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage());

            return response()->error($e->getMessage(), 500);
        }

        
        return response()->success(null, "Successfully Created Movie", 200);

    }

    public function updateMovie(Request $request,$movie_id)
    {
        $request->validate([
            'title' => 'required|min:5|max:30',
            'summary' => 'required|min:10',
            'imdbRating' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'pdfLink' => 'required',
            'authorId' => 'required|integer|exists:authors,id',
            'tags' => 'required|array',
            'tags.*' => 'required',
            'genres' => 'required|array',
            'genres.*' => 'required',
        ]);

        try {

            $movie = Movie::findOrFail($movie_id);

            DB::beginTransaction();

            if ($request->hasFile('coverImage')) {
                $photoPath = file_get_contents($request->coverImage);
                $photoData = base64_encode($photoPath);

                $movie->cover_image = $photoData;
                $movie->save();
            }

            $movie->update([
                'title' => $request->title,
                'summary' => $request->summary,
                'imdb_rating' => $request->imdbRating,
                'pdf_link' => $request->pdfLink,
                'author_id' => $request->authorId,
            ]);

            $movie->tags()->sync($request->tags);
            $movie->genres()->sync($request->genres);

             DB::commit();

        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage());

            return response()->error($e->getMessage(), 500);
        }

        return response()->success(null, "Successfully Updated Movie", 200);
    }

    public function destoryMovie($movie_id)
    {
        $movie = Movie::findOrFail($movie_id);

        $movie->delete();

        return response()->success(null, "Successfully Deleted Movie", 204);

    }

    public function storeComment(Request $request)
    {
        $request->validate([
            'comment' => 'required|min:3|string',
            'commenter' => 'required|email:rfc,dns',
            'movie_id' => 'required|integer|exists:movies,id'
        ]);

        try {

            DB::beginTransaction();

            Comment::create([
                'comment' => $request->comment,
                'commenter' => $request->commenter,
                'movie_id' => $request->movie_id,
            ]);

            DB::commit();


        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage());

            return response()->error($e->getMessage(), 500);
        }

        
        return response()->success(null, "Successfully Commented", 200);
    }


}
