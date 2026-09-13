import Image from "next/image";
import { Link } from "@/i18n/navigation";
import { getAllBlogPosts } from "@/lib/content";

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("es-CL", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export default async function BlogPage() {
  const posts = await getAllBlogPosts();

  return (
    <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
      <h1 className="font-display text-3xl font-bold text-brand-dark">Blog</h1>
      <p className="mt-3 max-w-2xl text-foreground/70">
        Consejos, guías rápidas e ideas para tu próximo viaje.
      </p>

      {posts.length === 0 ? (
        <p className="mt-10 text-foreground/60">Todavía no hay artículos publicados.</p>
      ) : (
        <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {posts.map((post) => (
            <Link
              key={post.slug}
              href={`/blog/${post.slug}`}
              className="shine-card group block overflow-hidden rounded-xl border border-black/5 bg-background"
            >
              <div className="relative aspect-[4/3] overflow-hidden bg-linear-to-br from-brand-dark to-brand">
                {post.featuredImage ? (
                  <Image
                    src={post.featuredImage.node.sourceUrl}
                    alt={post.title}
                    fill
                    sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                    className="object-cover transition duration-500 group-hover:scale-105"
                  />
                ) : (
                  <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    className="absolute left-1/2 top-1/2 h-16 w-16 -translate-x-1/2 -translate-y-1/2 fill-white/15"
                  >
                    <path d="M4 4h16v16H4V4Zm2 2v12h12V6H6Zm2 2h8v2H8V8Zm0 4h8v2H8v-2Zm0 4h5v2H8v-2Z" />
                  </svg>
                )}
              </div>
              <div className="p-4">
                <p className="text-xs font-semibold uppercase tracking-wide text-brand">
                  {formatDate(post.date)}
                </p>
                <h2 className="font-display mt-1 font-semibold text-brand-dark">
                  {post.title}
                </h2>
                <div
                  className="mt-2 text-sm text-foreground/70 [&_p]:m-0"
                  dangerouslySetInnerHTML={{ __html: post.excerpt }}
                />
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}
