import type { Config } from '@jest/types';

export default async (): Promise<Config.InitialOptions> => {
    return {
        roots: ['./assets/src'],
        transform: {
            '^.+\\.(ts|tsx)$': 'ts-jest',
        },
    };
};