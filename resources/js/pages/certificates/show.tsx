import { Head } from '@inertiajs/react';
import { Printer } from 'lucide-react';
import { useState } from 'react';
import CopyButton from '@/components/landing/copy-button';
import { Button } from '@/components/ui/button';
import type { Certificate } from '@/types/models';

type Signer = {
    name: string;
    title: string;
    signature_url: string;
};

type Props = {
    certificate: Certificate | { revoked: true };
    programme: string;
    signer: Signer;
};

function CertificateSheet({
    certificate,
    programme,
    signer,
}: {
    certificate: Certificate;
    programme: string;
    signer: Signer;
}) {
    const [hasSignature, setHasSignature] = useState(true);
    const nameSize =
        certificate.recipient_name.length > 28 ? 'text-[54px]' : 'text-[76px]';

    return (
        <div className="mx-auto h-[246px] w-[348px] sm:h-[429px] sm:w-[606px] lg:h-[715px] lg:w-[1011px] xl:h-[794px] xl:w-[1123px] print:h-[794px] print:w-[1123px]">
            <div className="certificate-sheet relative h-[794px] w-[1123px] origin-top-left scale-[0.31] overflow-hidden bg-black text-black sm:scale-[0.54] lg:scale-[0.9] xl:scale-100 print:scale-100">
                <svg
                    aria-hidden="true"
                    viewBox="0 0 1123 794"
                    className="absolute inset-0 size-full"
                >
                    <polygon
                        points="32,35 905,35 1091,316 1091,759 218,759 32,360"
                        fill="#ffd25e"
                    />
                    <polygon
                        points="905,35 930,35 1091,278 1091,316"
                        fill="#b3c0c4"
                    />
                    <polygon
                        points="930,35 985,35 1091,196 1091,278"
                        fill="#6b7681"
                    />
                    <polygon
                        points="985,35 1035,35 1091,120 1091,196"
                        fill="#4a555e"
                    />
                    <polygon
                        points="32,360 60,404 218,759 190,759"
                        fill="#9fb1b6"
                    />
                    <polygon
                        points="60,404 110,470 250,759 218,759"
                        fill="#6b7681"
                    />
                    <polygon
                        points="110,470 150,530 285,759 250,759"
                        fill="#c4d0d3"
                        opacity="0.9"
                    />
                    <g fill="none" strokeWidth="1.4">
                        <path
                            d="M930 420 L985 380 L1040 405 L1050 470 L985 500 L935 470 Z M985 380 L985 500 M935 470 L1040 405"
                            stroke="#1b1b1b"
                        />
                        <path
                            d="M985 500 L1050 470 L1091 525 L1080 590 L1020 610 L985 560 Z M1050 470 L1020 610"
                            stroke="#8b8f94"
                        />
                        <path
                            d="M870 570 L930 545 L985 560 L1000 640 L935 690 L880 650 Z M930 545 L935 690 M870 570 L1000 640"
                            stroke="#b8bcc0"
                        />
                    </g>
                </svg>

                <div className="absolute inset-0 flex flex-col items-center px-[150px] pt-[84px] text-center">
                    <h1 className="text-[54px] leading-none font-semibold tracking-[0.1em]">
                        CERTIFICATE
                    </h1>
                    <p className="mt-2 text-[23px] tracking-[0.12em] text-neutral-700">
                        OF PARTICIPATION
                    </p>
                    <p className="mt-10 text-[19px] font-semibold tracking-[0.35em] text-neutral-800">
                        IS PRESENTED TO
                    </p>
                    <p className={`font-script mt-4 leading-tight ${nameSize}`}>
                        {certificate.recipient_name}
                    </p>
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 640 12"
                        className="mt-1 h-3 w-[640px]"
                    >
                        <line
                            x1="0"
                            y1="6"
                            x2="270"
                            y2="6"
                            stroke="#111"
                            strokeWidth="1.2"
                        />
                        <line
                            x1="370"
                            y1="6"
                            x2="640"
                            y2="6"
                            stroke="#111"
                            strokeWidth="1.2"
                        />
                        <circle cx="320" cy="6" r="4" fill="#111" />
                        <circle cx="300" cy="6" r="2.4" fill="#111" />
                        <circle cx="340" cy="6" r="2.4" fill="#111" />
                    </svg>
                    <p className="mt-7 max-w-[700px] text-[23px] leading-snug font-bold tracking-wide uppercase">
                        For completing the {certificate.duration_days} day{' '}
                        {programme} in {certificate.year}
                    </p>

                    <div className="absolute bottom-[42px] left-1/2 flex w-[560px] -translate-x-1/2 flex-col items-center">
                        {hasSignature && (
                            <img
                                src={signer.signature_url}
                                alt=""
                                className="mb-1 h-[78px] w-auto"
                                onError={() => setHasSignature(false)}
                            />
                        )}
                        <p className="text-[17px] font-bold tracking-[0.14em] text-neutral-800 uppercase">
                            {signer.name}
                        </p>
                        <p className="font-display mt-1 max-w-[420px] text-[15px] leading-snug tracking-[0.08em] text-neutral-700 uppercase">
                            {signer.title}
                        </p>
                    </div>
                </div>

                <p className="absolute right-0 bottom-2 left-0 text-center text-[11px] tracking-[0.12em] text-neutral-400">
                    CERTIFICATE NO. {certificate.code.toUpperCase()} · ISSUED{' '}
                    {certificate.issued_at}
                </p>
            </div>
        </div>
    );
}

export default function CertificateShow({
    certificate,
    programme,
    signer,
}: Props) {
    const isRevoked = 'revoked' in certificate && certificate.revoked;
    const shareUrl = typeof window === 'undefined' ? '' : window.location.href;

    return (
        <>
            <Head
                title={
                    isRevoked ? 'Certificate' : 'Certificate of Participation'
                }
            />

            <div className="min-h-svh bg-neutral-100 px-4 py-8 text-neutral-900 print:bg-white print:p-0">
                {isRevoked ? (
                    <div className="mx-auto max-w-md rounded-2xl bg-white p-8 text-center shadow-xs">
                        <h1 className="text-xl font-semibold">
                            This certificate is no longer valid
                        </h1>
                        <p className="mt-2 text-sm text-neutral-600">
                            It has been revoked by the programme. If you think
                            this is a mistake, please contact the organisers.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="mx-auto mb-6 flex max-w-[1123px] flex-wrap items-center justify-between gap-3 print:hidden">
                            <div>
                                <h1 className="text-lg font-semibold">
                                    Certificate of Participation
                                </h1>
                                <p className="text-sm text-neutral-600">
                                    Print it, or choose &ldquo;Save as
                                    PDF&rdquo; in the print window.
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <CopyButton
                                    value={shareUrl}
                                    label="Copy link"
                                    announcement="Certificate link copied"
                                    variant="quiet"
                                />
                                <Button
                                    type="button"
                                    onClick={() => window.print()}
                                >
                                    <Printer aria-hidden="true" />
                                    Print or save PDF
                                </Button>
                            </div>
                        </div>

                        <CertificateSheet
                            certificate={certificate as Certificate}
                            programme={programme}
                            signer={signer}
                        />
                    </>
                )}
            </div>
        </>
    );
}
