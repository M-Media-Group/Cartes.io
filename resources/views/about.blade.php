@extends('layouts.app')

@section('content')
<h1>About {{ config('app.name') }}</h1>
<p>Hey! I'm Michał, and I've created a few blogs to help people visiting the South of France, including this one, {{ config('app.name') }}.</p>
<p>I'm a former student of the university in Monaco and now reside in Villefranche sur Mer.</p>
<p>The South of France itself is incredible, offering rich scenery, culture, and gastronomy, but the technology-mindset is still lacking. Because of this, a lot of breathtaking areas remain unexplored by visitors. This blog aims to fix that.</p>
@markdown
With this blog, I want to bring people closer to the extensive opportunities available in the region. If you live in the area, you can [contribute articles](/register) to this blog or any one of the other blogs!

See all the blogs:
- [Explore Villefranche](https://explorevillefranche.com)
- [Explore South of France](https://exploresouthoffrance.com)

*Coming soon*
- [Explore Saint Jean Cap Ferrat](https://exploresaintjeancapferrat.com)
- [Explore Beaulieu](https://explorebeaulieu.com)
- [Explore Eze Village](https://exploreezevillage.com)
- [Explore Antibes](https://exploreantibes.com)
- [Explore Menton](https://explorementon.com)
- [Explore St Tropez](https://exploresttropez.com)
- [Explore Avignon](https://exploreavignon.com)
---
Like this website and need one for your business? [Contact me](http://mmediagroup.fr)!
@endmarkdown


@endsection
