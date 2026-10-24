import { Head, usePage } from '@inertiajs/react';

type SeoProps = {
    title: string;
    description: string;
    image?: string;
};

export default function Seo({
    title,
    description,
    image = '/images/hero.webp',
}: SeoProps) {
    const { name, canonicalUrl, appUrl } = usePage().props;
    const imageUrl = image.startsWith('http') ? image : `${appUrl}${image}`;

    return (
        <Head title={title}>
            <meta
                head-key="description"
                name="description"
                content={description}
            />
            <link head-key="canonical" rel="canonical" href={canonicalUrl} />
            <meta head-key="og:type" property="og:type" content="website" />
            <meta
                head-key="og:site_name"
                property="og:site_name"
                content={name}
            />
            <meta head-key="og:title" property="og:title" content={title} />
            <meta
                head-key="og:description"
                property="og:description"
                content={description}
            />
            <meta head-key="og:url" property="og:url" content={canonicalUrl} />
            <meta head-key="og:image" property="og:image" content={imageUrl} />
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content="summary_large_image"
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={title}
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                content={description}
            />
            <meta
                head-key="twitter:image"
                name="twitter:image"
                content={imageUrl}
            />
        </Head>
    );
}
