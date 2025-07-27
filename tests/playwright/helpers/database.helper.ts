import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);

export class DatabaseHelper {
  async clearDatabase(): Promise<void> {
    try {
      await execAsync('symfony console doctrine:database:drop --if-exists --force --env=test');
      await execAsync('symfony console doctrine:database:create --env=test');
      await execAsync('symfony console doctrine:migrations:migrate --env=test --no-interaction');
    } catch (error) {
      console.error('Failed to clear database:', error);
    }
  }
  
  async loadFixtures(): Promise<void> {
    try {
      await execAsync('symfony console doctrine:fixtures:load --group=data --env=test --no-interaction');
      await execAsync('symfony console doctrine:fixtures:load --group=rental --append --env=test --no-interaction');
    } catch (error) {
      console.error('Failed to load fixtures:', error);
    }
  }
  
  async createTestUser(email: string, password: string): Promise<void> {
    // This could be implemented to directly create a user in the database
    // For now, we'll rely on the registration flow in tests
  }
}