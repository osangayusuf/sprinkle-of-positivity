import { Head, Link } from '@inertiajs/react';
import { Award, ChevronRight } from 'lucide-react';
import { PageHeader } from '@/components/page-header';
import type { Certificate } from '@/types/models';

export default function CertificatesIndex({
    certificates,
}: {
    certificates: Certificate[];
}) {
    return (
        <>
            <Head title="Certificates" />
            <PageHeader title="Certificates" backHref="/profile" />

            <div className="flex flex-col gap-3 px-4 py-4">
                {certificates.map((certificate) => (
                    <Link
                        key={certificate.id}
                        href={`/certificates/${certificate.code}`}
                        className="bg-muted flex items-center gap-3 rounded-2xl p-4"
                    >
                        <Award className="text-primary size-6 shrink-0" />
                        <div className="flex-1">
                            <p className="text-sm font-semibold">
                                {certificate.group_name}
                            </p>
                            <p className="text-muted-foreground text-xs">
                                {certificate.duration_days}-day challenge,{' '}
                                {certificate.year}
                            </p>
                        </div>
                        <ChevronRight className="text-muted-foreground size-4" />
                    </Link>
                ))}

                {certificates.length === 0 && (
                    <div className="bg-muted text-muted-foreground rounded-2xl p-6 text-center text-sm">
                        Complete every day of a challenge and your certificate
                        will appear here.
                    </div>
                )}
            </div>
        </>
    );
}
