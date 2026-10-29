export type DonationAccount = {
    label: string;
    currency: 'NGN' | 'USD';
    bank: string;
    accountName: string;
    accountNumber: string;
};

export const support = {
    intro: 'The study runs freely and no gift is required to take part. If you would like to support the work and the community, you are welcome to give what you can, in the currency that suits you.',
    accounts: [
        {
            label: 'Naira account',
            currency: 'NGN',
            bank: 'Providus Bank',
            accountName: 'The Service Excellence Mentor Enterprise',
            accountNumber: '5401542407',
        },
        {
            label: 'US dollar domiciliary account',
            currency: 'USD',
            bank: 'Providus Bank',
            accountName: 'The Service Excellence Mentor Enterprise',
            accountNumber: '5401558781',
        },
        {
            label: 'US dollar cash domiciliary account',
            currency: 'USD',
            bank: 'Providus Bank',
            accountName: 'The Service Excellence Mentor Enterprise',
            accountNumber: '8501558778',
        },
    ] satisfies DonationAccount[],
};
