<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Repository Cache Settings
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration settings for cache durations.
    | Values can be overridden using environment variables.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Repository Listing Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache repository listings (in minutes)
    |
    */
    'repositories_list_duration' => env('CACHE_REPOSITORIES_LIST_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Repository Details Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache individual repository details (in minutes)
    |
    */
    'repository_details_duration' => env('CACHE_REPOSITORY_DETAILS_MINUTES', 60),

    /*
    |--------------------------------------------------------------------------
    | Repository File Content Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache file contents from repositories (in minutes)
    |
    */
    'file_content_duration' => env('CACHE_FILE_CONTENT_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Repository Folder Content Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache folder structure/tree from repositories (in minutes)
    |
    */
    'folder_content_duration' => env('CACHE_FOLDER_CONTENT_MINUTES', 20),

    /*
    |--------------------------------------------------------------------------
    | User Information Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache user profile information (in minutes)
    |
    */
    'user_info_duration' => env('CACHE_USER_INFO_MINUTES', 120),
];
