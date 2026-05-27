# Directorist Author Rating

Directorist Author Rating is a WordPress extension for the Directorist plugin. It tracks review data at the listing author level, so each author can have a saved average rating, review count, review ID list, and a dashboard view of received reviews.

## Requirements

- WordPress 5.8 or newer
- PHP 7.4 or newer
- Directorist plugin installed and active

Directorist is required. The plugin will not activate correctly unless Directorist is active.

## Features

- Stores author-level average rating in user meta: `directorist_rating`
- Stores author-level total review count in user meta: `directorist_review_count`
- Stores received review IDs in user meta: `directorist_reviews`
- Automatically updates author rating data when a Directorist listing review is submitted, edited, approved, unapproved, trashed, spammed, or deleted
- Adds author rating display to the Directorist author profile header
- Adds author rating display to the single listing Author Info section
- Adds a dashboard tab named **User Reviews**
- Displays received reviews in list view inside the dashboard
- Shows review rating, reviewer name, review date, review content, status, and related listing

## Download From GitHub

1. Open the GitHub repository page for this plugin.
2. Click the green **Code** button.
3. Click **Download ZIP**.
4. Save the ZIP file to your computer.

If the downloaded ZIP contains a folder name like `directorist-author-rating-main`, WordPress can still upload it, but the cleaner plugin folder name is `directorist-author-rating`.

## Install From WordPress Admin Panel

1. Log in to your WordPress admin panel.
2. Go to **Plugins > Add New**.
3. Click **Upload Plugin**.
4. Click **Choose File** and select the ZIP file downloaded from GitHub.
5. Click **Install Now**.
6. After installation finishes, click **Activate Plugin**.

Make sure the Directorist plugin is already installed and active before activating Directorist Author Rating.

## How It Works

When a review is submitted for a Directorist listing, this plugin finds the listing author and updates that author's user meta:

- `directorist_reviews`: stores review IDs in an array
- `directorist_review_count`: stores the total number of received reviews
- `directorist_rating`: stores the average rating

The average rating is displayed with one decimal point, for example `2.7`.

## Dashboard Tab

The plugin uses the `directorist_dashboard_tabs` hook to add a new dashboard tab:

**User Reviews**

This tab lists the reviews received by the logged-in listing author. Each review card includes the rating, reviewer name, date, review text, status, and listing link.

## Plugin Information

- Plugin Name: Directorist Author Rating
- Version: 2.0.0
- Author: wpXplore
- Text Domain: `directorist-author-rating`

