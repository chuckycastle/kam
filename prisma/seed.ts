import { PrismaClient, Role } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main(): Promise<void> {
  console.log('Seeding database...');

  // Create categories
  const categories = await Promise.all([
    prisma.category.upsert({
      where: { id: 'cat-restaurant' },
      update: {},
      create: {
        id: 'cat-restaurant',
        title: 'Restaurant',
        description: 'Food and dining establishments',
      },
    }),
    prisma.category.upsert({
      where: { id: 'cat-retail' },
      update: {},
      create: {
        id: 'cat-retail',
        title: 'Retail',
        description: 'Stores and shopping',
      },
    }),
    prisma.category.upsert({
      where: { id: 'cat-services' },
      update: {},
      create: {
        id: 'cat-services',
        title: 'Services',
        description: 'Professional services',
      },
    }),
    prisma.category.upsert({
      where: { id: 'cat-entertainment' },
      update: {},
      create: {
        id: 'cat-entertainment',
        title: 'Entertainment',
        description: 'Entertainment and recreation',
      },
    }),
    prisma.category.upsert({
      where: { id: 'cat-other' },
      update: {},
      create: {
        id: 'cat-other',
        title: 'Other',
        description: 'Miscellaneous donations',
      },
    }),
  ]);

  console.log(`Created ${categories.length} categories`);

  // Create admin user
  const adminPassword = await bcrypt.hash('Admin123!', 12);
  const admin = await prisma.user.upsert({
    where: { email: 'admin@kriegercenter.org' },
    update: {},
    create: {
      email: 'admin@kriegercenter.org',
      username: 'admin',
      passwordHash: adminPassword,
      firstName: 'Admin',
      lastName: 'User',
      role: Role.SUPER_ADMIN,
    },
  });

  console.log(`Created admin user: ${admin.email}`);

  // Create demo volunteer
  const volunteerPassword = await bcrypt.hash('Volunteer123!', 12);
  const volunteer = await prisma.user.upsert({
    where: { email: 'volunteer@example.com' },
    update: {},
    create: {
      email: 'volunteer@example.com',
      username: 'volunteer',
      passwordHash: volunteerPassword,
      firstName: 'Demo',
      lastName: 'Volunteer',
      role: Role.VOLUNTEER,
    },
  });

  console.log(`Created volunteer user: ${volunteer.email}`);

  // Create sample organizations
  const orgs = await Promise.all([
    prisma.organization.upsert({
      where: { id: 'org-sample-1' },
      update: {},
      create: {
        id: 'org-sample-1',
        name: 'Sample Restaurant',
        description: 'A local family restaurant',
        contactName: 'John Smith',
        contactTitle: 'Owner',
        contactPhone: '555-123-4567',
        contactEmail: 'john@samplerestaurant.com',
        categoryId: 'cat-restaurant',
      },
    }),
    prisma.organization.upsert({
      where: { id: 'org-sample-2' },
      update: {},
      create: {
        id: 'org-sample-2',
        name: 'Sample Retail Store',
        description: 'Local retail shop',
        contactName: 'Jane Doe',
        contactTitle: 'Manager',
        contactPhone: '555-987-6543',
        contactEmail: 'jane@sampleretail.com',
        categoryId: 'cat-retail',
      },
    }),
  ]);

  console.log(`Created ${orgs.length} sample organizations`);

  console.log('Database seeding completed!');
}

main()
  .catch((e) => {
    console.error('Error seeding database:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
