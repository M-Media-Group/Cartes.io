@extends('layouts.clean')

@section('content')
<h1>Write for {{config('app.name')}}</h1>
@markdown
Before you start publishing articles, we want you to read over the following guidelines of our blog:

- Use basic [Markdown](https://www.markdownguide.org/basic-syntax/) formatting, but don’t use Heading level 1 tags (that’s the heading with 1 `#`). Here’s an example of what *not to do* for a heading:

	`# Citadel`

	Instead, use

	`## Citadel`

- Link to other categories simply by writing a tag with no space after it, like this example: #sights

- Your articles must be in proper English, free from bias and favouritism

- Refrain from suggesting people visit other cities, we want to keep them in this one

- When choosing an article title and writing the excerpt, remember to think about what the person getting to your blog is likely to search for on Google, and then use those terms. The title and excerpt directly relate to the SEO and search ranking of the blog

- Upload one high quality jpeg as the header image, the suggested and maximum width is 1280px, and the file size should not be larger than half a megabyte. Make sure to process the file with [jpeg.io](https://jpeg.io) before uploading it.

- Opinion pieces or irrelevant content isn’t allowed

- if including images in your article, you can upload them anywhere you like and then provide a link to the image in Markdown format:
	`![Image title](https://link.to/image.png)`

- You must hold full rights to any image you upload

- Don't promote products, services, or businesses

@endmarkdown
    @if(Auth::user()->canNot('create posts') && Auth::user()->hasVerifiedEmail())
    	<a href="#" class="btn btn-primary mb-3" onclick="event.preventDefault();
                                             document.getElementById('writer-form').submit();">I understand and am ready to write
        </a>
        <form id="writer-form" action="/me/apply/writer" method="POST" style="display: none;">
            @csrf
        </form>
   	@elseif(!Auth::user()->hasVerifiedEmail())
    	<p class="text-danger font-weight-bold">You must verify your email to publish posts.</p>
    @endif
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">Go back</a>
@endsection
