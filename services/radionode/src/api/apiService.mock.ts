import { IApiService } from './apiService';

const createApiServiceMock = (): IApiService => ({
  getNowPlaying: jest.fn(),
});

export default createApiServiceMock;
