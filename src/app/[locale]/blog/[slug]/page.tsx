import Image from "next/image";
import { notFound } from "next/navigation";
import { getBlogPostBySlug } from "@/lib/content";

function formatDate(iso: string) {
  return new Date(iso).toLocaleDateString("es-CL", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

export default async function BlogPostPage({
  params,
}: PageProps<"/[locale]/blog/[slug]">) {
  const { slug } = await params;
  const post = await getBlogPostBySlug(slug);

  if (!post) notFound();

  return (
    <article className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
      <p className="text-xs font-semibold uppercase tracking-wide text-brand">
        {formatDate(post.date)}
      </p>
      <h1 className="font-display mt-2 text-3xl font-bold text-brand-dark sm:text-4xl">
        {post.title}
      </h1>

      {post.featuredImage && (
        <div className="relative mt-8 aspect-video overflow-hidden rounded-xl">
          <Image
            src={post.featuredImage.node.sourceUrl}
            alt={post.title}
            fill
            sizes="(min-width: 768px) 768px, 100vw"
            className="object-cover"
            priority
          />
        </div>
      )}

      <div
        className="prose prose-neutral mt-8 max-w-none"
        dangerouslySetInnerHTML={{ __html: post.content }}
      />
    </article>
  );
}
